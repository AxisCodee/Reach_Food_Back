<?php

namespace App\Services;

use App\Enums\Roles;
use App\Helpers\ResponseHelper;
use App\Models\Branch;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class NotificationService
{


    private readonly ?User $user;
    private mixed $actionable;

    private array $types = [
        Branch::class => 'branch',
        User::class => 'user',
        Trip::class => 'trip',
        Order::class => 'order',
        Product::class => 'product',
    ];

    private array $translate = [
        'delete' => 'حذف',
        'update' => 'تعديل',
        'add' => 'إضافة',
        'cancel' => 'إلغاء',
        'branch' => 'الفرع',
        'user' => 'المستخدم',
        'trip' => 'الرحلة',
        'order' => 'الطلب',
        'product' => 'منتج',
        'Sunday' => 'الأحد',
        'Monday' => 'الاثنين',
        'Tuesday' => 'الثلاثاء',
        'Wednesday' => 'الأربعاء',
        'Thursday' => 'الخميس',
        'Friday' => 'الجمعة',
        'Saturday' => 'السبت',
    ];

    private array $translateAction = [
        'delete' => 'حذف',
        'update' => 'تعديل',
        'add' => 'أضاف',
        'cancel' => 'إلغاء',
    ];

    public function __construct(
        private readonly Notification $notification,
    )
    {
        $this->user = $this->notification['user'];
        $this->actionable = $this->notification['actionable'];
    }

    public function getType(): string
    {
        if ($this->notification['action_type'] === 'late') {
            return "عدم خروج مندوب في رحلة بعد مرور ساعة من موعد انطلاقها";
        }

        return sprintf(
            '%s %s',
            $this->translate[$this->notification['action_type']],
            $this->translate[$this->types[$this->notification['actionable_type']]]
        );
    }

    public function getContent($id = null): string
    {
        switch ($this->notification['action_type']) {
            case 'start_trip':
                return $this->handleStartTrip();
            case 'change_price':
                return 'تم تعديل نشرة الأسعار.';
            case 'cancel':
                return $this->handleCancel();
            case 'late':
                return $this->handleLate();
            case 'trace':
                return $this->handleTrace($id);
            case 'back':
                return $this->handleBack();
            default:
                return $this->handleDefault();
        }
    }

    private function handleStartTrip(): string
    {
        $address = $this->actionable['trip']['address']['city']['name'];
        return "حان موعد رحلة $address";
    }

    private function handleCancel(): string
    {
        if (auth()->user()?->role === Roles::SALES_MANAGER->value) {
            return sprintf(
                "قام %s بإلغاء الطلب %s للزبون %s",
                $this->user['name'],
                $this->actionable['id'],
                $this->actionable['customer']['name']
            );
        } else {
            return $this->handleSalesmanOrCustomer();
        }
    }

    private function handleLate(): string
    {
        $salesmanName = $this->actionable['trip']['salesman']['name'];
        $tripId = $this->actionable['id'];
        return "لم يخرج المندوب $salesmanName في الرحلة $tripId بعد";
    }

    private function handleBack(): string
    {
        return sprintf("قام المندوب %s بالتراجع عن إلغاء طلبك رقم %d",
            $this->user['name'],
            $this->notification['actionable_id']);
    }

    private function handleTrace($id): string
    {
        $delay = $this->actionable['delay'];
        $customer = $this->actionable['trip']->customerTimes()->where('customer_id', $id)->first();
        $time = Carbon::make($customer['arrival_time'])->addMinutes($delay)->format('H:i');

        if ($this->actionable['tripTrace']['status'] === 'start') {
            return sprintf(" بدأ %s الرحلة وقت الوصول المتوقع %s", $this->user['name'], $time);
        }

        return sprintf(' تم تغيير الوقت الوصول المتوقع للساعة %s', $time);
    }

    private function handleDefault(): string
    {
        if ($this->isSalesmanOrCustomer() && $this->notification['actionable_type'] === Order::class) {
            return $this->handleSalesmanOrCustomer();
        }

        $action = $this->translateAction[$this->notification['action_type']];
        $type = $this->translate[$this->types[$this->notification['actionable_type']]];
        $typePrefix = $this->notification['action_type'] === 'update' ? 'على ' : '';
        $complete = $this->getCompleteMessage();

        return sprintf("%s %s %s %s %s", $action, $this->user['name'], $typePrefix, $type, $complete);
    }

    private function handleSalesmanOrCustomer(): string

    {
        $complete =  'طلب';
        $complete .= $this->user['role'] === 'customer' ? 'ه' : 'ك';
        return sprintf(
            "قام %s %s ب%s %s رقم %s",
            $this->user['role'] === 'salesman' ? 'المندوب' : 'الزبون',
            $this->user['name'],
            $this->translateAction[$this->notification['action_type']],
            $complete,
            $this->notification['actionable_id']
        );
    }

    private function getCompleteMessage(): string
    {
        $actionableType = $this->types[$this->notification['actionable_type']];

        if ($actionableType === 'order') {
            return sprintf(" الرقم %s", $this->actionable['id']);
        }

        if ($actionableType === 'trip') {
            return sprintf(" الرقم %s اليوم %s", $this->actionable['id'], $this->translate[$this->actionable['day']]);
        }

        return $this->actionable['name'];
    }

    private function isSalesmanOrCustomer(): bool
    {
        return in_array($this->user['role'], ['salesman', 'customer']);
    }


    public function getTitle(): string
    {
        if ($this->notification['action_type'] == 'change_price' || $this->notification['action_type'] == 'start_trip') {
            return 'اسم التطبيق';
        }
        return $this->user['name'];
    }

    public static function make($data, bool $firstOrCreate, array $ownerIds): ?NotificationService
    {
        if (!count($ownerIds))
            return null;
        $notification = Notification::query();
        $notification = $firstOrCreate ?
            $notification->updateOrCreate($data, ['updated_at' => now()]) :
            $notification->create($data);
        $attaching = [];
        foreach ($ownerIds as $ownerId) {
            $attaching[$ownerId] = [
                'read' => 0
            ];
        }
        $notification->users()->syncWithoutDetaching($attaching);

        return new NotificationService($notification);
    }

    public static function setRead(int $userId): void
    {
        DB::table('user_notifications')
            ->where('owner_id', '=', $userId)
            ->update([
                'read' => true
            ]);
    }

    public static function unReadCount(int $userId, ?int $branchId): int
    {
        return DB::table('user_notifications')
            ->where('owner_id', '=', $userId)
            ->where('read', '=', false)
            ->join('notifications', 'notifications.id', '=', 'user_notifications.notification_id')
            ->where(function (Builder $query) use ($branchId) {
                $query
                    ->where('notifications.branch_id', '=', $branchId)
                    ->orWhereNull('notifications.branch_id');
            })
            ->count();
    }


    private static array $canBack = [
        Product::class,
        Branch::class,
        User::class,
        Trip::class
    ];

    public static function back(Notification $notification)
    {
        $actionableType = $notification['actionable_type'];
        if ($notification['action_type'] == 'delete' && in_array($actionableType, self::$canBack)) {
            if ($actionableType == Trip::class) {
                $tripService = new TripService();
                if ($tripService->conflicts($notification['actionable'])) {
                    return ResponseHelper::error('لا يمكن ارجاع هذه الرحلة');
                }
            }
            if ($actionableType == Branch::class) {
                $notification->actionable->update([
                    'name' => (new CityServices())->getNameOfBranch($notification->actionable['city_id'], $notification->actionable['name'])
                ]);
                $notification->actionable->salesManagers()->restore();
            }
            $notification->actionable->restore();
            $notification->delete();
            return ResponseHelper::success('success back');
        } else {
            return ResponseHelper::error('لا يمكن التراجع عن هذا الحدث');
        }
    }
}

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
        'update' => 'عدل',
        'add' => 'أضاف',
        'cancel' => 'الغى',
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
        if ($this->notification['action_type'] == 'late') {
            return "عدم خروج مندوب في رحلة بعد مرور ساعة من موعد انطلاقها";
        }
        return
            $this->translate[$this->notification['action_type']]
            . ' ' .
            $this->translate[$this->types[$this->notification['actionable_type']]];
    }

    public function getContent(): string
    {

        if ($this->notification['action_type'] == 'start_trip') {
            $address = $this->actionable['trip']['address']['city']['name'];
            return "حان موعد رحلة $address ";
        }

        if ($this->notification['action_type'] == 'change_price') {
            return 'تم تعديل قائمة الأسعار';
        }

        if ($this->notification['action_type'] == 'cancel') {
            if (auth()->user()?->role == Roles::SALES_MANAGER->value)
                return "{$this->actionable['customer']['name']} للزبون  {$this->actionable['id']}  بإلغاء الطلب  {$this->user['name']} قام ";
        }

        if ($this->notification['action_type'] == 'late') {
            $salesmanName = $this->actionable['trip']['salesman']['name'];
            $tripId = $this->actionable['id'];
            return "لم يخرج المندوب $salesmanName في الرحلة $tripId بعد ";
        }

        if ($this->notification['action_type'] == 'trace') {
            $time = Carbon::make($this->actionable['arrival_time']);
            $trace = $this->actionable['trip']->dates()
                ->where('start_date', '=', Carbon::today()
                    ->toDateString())
                ->first();
            logger($trace);
            $delay = $trace['delay'];
            $time->addMinutes($delay);

            $time = $time->format('H:i');
            if ($trace['tripTrace']['status'] == 'start')
                return " بدأ {$this->user['name']} الرحلة وقت الوصول المتوقع $time"; // todo add hour
            else
                return ' تم تغيير الوقت الوصول المتوقع للساعة  ' . $time; // todo add hour
        }


        if (($this->user['role'] == 'salesman' || $this->user['role'] == 'customer') && $this->notification['actionable_type'] == Order::class) {
            $complete = $this->notification['action_type'] == 'update' ?
                ' على طلب' :
                ' طلب';
            $complete .= $this->user['role'] == 'customer' ? 'ه' : 'ك';
            return "{$this->translateAction[$this->notification['action_type']]} {$this->user['name']} $complete رقم {$this->notification['actionable_id']}";
        }

        $action = $this->translateAction[$this->notification['action_type']];
        $type = $this->translate[$this->types[$this->notification['actionable_type']]];
        $type = ($this->notification['action_type'] == 'update' ? 'على ' : '') . $type;
        if ($this->types[$this->notification['actionable_type']] == 'order') {
            $complete = " الرقم$this->actionable['id']";
        } else if ($this->types[$this->notification['actionable_type']] == 'trip') {
            $complete = " الرقم {$this->actionable['id']} اليوم {$this->translate[$this->actionable['day']]}";
        } else {
            $complete = $this->actionable['name'];
        }

        return "$action {$this->user['name']} $type $complete";
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

    public static function unReadCount(int $userId): int
    {
        return DB::table('user_notifications')
            ->where('owner_id', '=', $userId)
            ->where('read', '=', false)
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
            }
            $notification->actionable->restore();
            $notification->delete();
            return ResponseHelper::success('success back');
        } else {
            return ResponseHelper::error('لا يمكن التراجع عن هذا الحدث');
        }
    }
}

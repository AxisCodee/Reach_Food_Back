<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;

class NotificationService
{

    private readonly User $user;
    private mixed $actionable;

    private array $types = [
        Branch::class => 'branch',
        User::class => 'user',
        Trip::class => 'trip',
        Order::class => 'order',
    ];

    private array $translate = [
        'delete' => 'حذف',
        'update' => 'تعديل',
        'add'    => 'إضافة',
        'branch' => 'الفرع',
        'user'   => 'المستخدم',
        'trip'   => 'الرحلة',
        'order'  => 'الطلب',
        'Sunday' => 'الأحد',
        'Monday' => 'الاثنين',
        'Tuesday' => 'الثلاثاء',
        'Wednesday' => 'الاربعاء',
        'Thursday' => 'الخميس',
        'Friday' => 'الجمعة',
        'Saturday' => 'السبت',
    ];

    private array $translateAction = [
        'delete' => 'حذف',
        'update' => 'عدل',
        'add'    => 'إضاف',
    ];

    public function __construct(
        private readonly Notification $notification
    )
    {
        $this->user = $this->notification['user'];
        $this->actionable = $this->notification['actionable'];
    }

    public function getType(): string
    {
        return
            $this->translate[$this->notification['action_type']]
            . ' ' .
            $this->translate[$this->types[$this->notification['actionable_type']]];
    }

    public function getContent(): string
    {
        if ($this->notification['action_type'] == 'change_price'){
            return 'تم تعديل قائمة الأسعار';
        }

        if ($this->notification['action_type'] == 'cancel'){
            return  "{$this->actionable['customer']['name']} للزبون  {$this->actionable['id']}  بإلغاء الطلب  {$this->user['name']} قام ";
        }

        if($this->notification['action_type'] == 'trace'){
            $time = Carbon::make($this->actionable['arrival_time']);
            $trace = $this->actionable['trip']->dates()
                ->where('start_date', '=', Carbon::today()
                ->toDateString())
                ->first();
            logger($trace);
            $delay = $trace['delay'];
            $time->addMinutes($delay);

            $time = $time->format('H:i');
            if($trace['tripTrace']['status'] == 'start')
//                return "$time  الرحلة وقت الوصول المتوقع  {$this->user['name']} بدأ "; // todo add hour
                return " بدأ {$this->user['name']} الرحلة وقت الوصول المتوقع $time"; // todo add hour
            else
                return  ' تم تغيير الوقت الوصول المتوقع للساعة  '. $time ; // todo add hour
        }


        if($this->user['role'] == 'salesman' || $this->user['role'] == 'customer'){
            $complete = $this->notification['action_type'] == 'delete' ?
                ' طلب' :
                ' على طلب' ;
            $complete .= $this->user['role'] == 'customer' ? 'ه' : 'ك';
            return "{$this->translateAction[$this->notification['action_type']]} {$this->user['name']} $complete رقم {$this->actionable['id']}";
        }

        $action = $this->translateAction[$this->notification['action_type']];
        $type = $this->translate[$this->types[$this->notification['actionable_type']]];
        $type = ($this->notification['action_type'] == 'update' ? 'على ' : '') . $type;
        if($this->types[$this->notification['actionable_type']] == 'order'){
            $complete =  " الرقم$this->actionable['id']";
        }
        else if($this->types[$this->notification['actionable_type']] == 'trip'){
            $complete =  " الرقم {$this->actionable['id']} اليوم {$this->translate[$this->actionable['day']]}" ;
        }
        else {
            $complete = $this->actionable['name'];
        }

        return  "$action {$this->user['name']}  $complete $type";
    }

    public function getTitle(): string{
        if($this->notification['action_type'] == 'change_price'){
            return 'اسم التطبيق';
        }
        return $this->user['name'];
    }

}

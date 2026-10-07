<?php

namespace App\Notifications;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CourseAccessGrantedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Course $course,
        public string $reason = 'granted',
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $message = match ($this->reason) {
            'purchase_approved' => __('Your purchase was approved: :course', ['course' => $this->course->title]),
            'team_granted' => __('You received access via your team: :course', ['course' => $this->course->title]),
            default => __('You received course access: :course', ['course' => $this->course->title]),
        };

        return [
            'type' => 'course_access',
            'reason' => $this->reason,
            'course_id' => $this->course->id,
            'course_title' => $this->course->title,
            'message' => $message,
            'url' => route('courses.show', $this->course),
        ];
    }
}

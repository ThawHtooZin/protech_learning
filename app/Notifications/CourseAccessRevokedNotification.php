<?php

namespace App\Notifications;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CourseAccessRevokedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Course $course,
        public string $reason = 'revoked',
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $message = match ($this->reason) {
            'team_revoked' => __('Team access was removed: :course', ['course' => $this->course->title]),
            default => __('Course access was removed: :course', ['course' => $this->course->title]),
        };

        return [
            'type' => 'course_access_revoked',
            'reason' => $this->reason,
            'course_id' => $this->course->id,
            'course_title' => $this->course->title,
            'message' => $message,
            'url' => route('notifications.index'),
        ];
    }
}

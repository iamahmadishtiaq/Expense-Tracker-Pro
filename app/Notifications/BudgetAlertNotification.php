<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BudgetAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $categoryName,
        public float $spent,
        public float $budget,
        public float $percentage,
        public bool $isExceeded
    ) {}

    public function via(object $notifiable): array
    {
        return ['database']; // App ke andar bell icon ke liye
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->isExceeded ? '🚨 Budget Exceeded!' : '⚠️ Budget Warning (80%)',
            'message' => "You have spent " . format_currency($this->spent) . " of your " . format_currency($this->budget) . " budget for {$this->categoryName} ({$this->percentage}%).",
            'type' => $this->isExceeded ? 'danger' : 'warning',
            'url' => route('budgets.index'),
            'category' => $this->categoryName,
        ];
    }
}
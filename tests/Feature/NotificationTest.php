<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\LibraryNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_admin_can_view_and_manage_notifications(): void
    {
        $admin = User::factory()->admin()->create();
        Notification::send($admin, new LibraryNotification(
            title: 'Overdue book',
            message: 'A book is overdue.',
            tone: 'danger',
            url: route('admin.borrowings.index'),
        ));

        $this->actingAs($admin)->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee('Overdue book');

        $notification = $admin->notifications()->firstOrFail();
        $this->assertNull($notification->read_at);

        $this->actingAs($admin)->post(route('admin.notifications.read', $notification->id))->assertRedirect();
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_admin_can_mark_all_notifications_as_read(): void
    {
        $admin = User::factory()->admin()->create();
        Notification::send($admin, new LibraryNotification('First', 'One'));
        Notification::send($admin, new LibraryNotification('Second', 'Two'));

        $this->actingAs($admin)->post(route('admin.notifications.read-all'))->assertRedirect();

        $this->assertSame(0, $admin->fresh()->unreadNotifications()->count());
        $this->assertSame(2, $admin->fresh()->notifications()->count());
    }

    public function test_admin_cannot_mark_another_admins_notification_as_read(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->admin()->create();
        Notification::send($other, new LibraryNotification('Private', 'For another admin'));

        $notification = $other->notifications()->firstOrFail();

        $this->actingAs($admin)->post(route('admin.notifications.read', $notification->id))->assertNotFound();
        $this->assertNull($notification->fresh()->read_at);
    }
}

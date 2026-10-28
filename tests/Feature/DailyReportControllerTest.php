<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyReportControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'テストユーザー',
        ]);
        $this->actingAs($this->user);
    }

    public function test_index_returns_five_reports_per_page_in_descending_order(): void
    {
        foreach (range(1, 12) as $number) {
            $this->createReport($this->user, $number);
        }

        $firstPage = $this->getJson('/api/daily-reports');

        $firstPage->assertOk()
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.total', 12)
            ->assertJsonPath('data.0.title', '日報 12')
            ->assertJsonPath('data.4.title', '日報 8')
            ->assertJsonPath('data.0.user.name', 'テストユーザー');

        $this->assertCount(5, $firstPage->json('data'));

        $secondPage = $this->getJson('/api/daily-reports?page=2');

        $secondPage->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('data.0.title', '日報 7')
            ->assertJsonPath('data.4.title', '日報 3');

        $this->assertCount(5, $secondPage->json('data'));
    }

    public function test_store_assigns_the_authenticated_user_and_validates_fields(): void
    {
        $this->postJson('/api/daily-reports', [
            'reported_at' => '2026-10-27',
            'title' => '作業報告',
            'content' => '本日の作業内容を報告します。',
            'status' => 'submitted',
            'user_id' => 999,
        ])->assertCreated()
            ->assertJsonPath('data.user_id', $this->user->id)
            ->assertJsonPath('data.user.name', 'テストユーザー');

        $this->assertDatabaseHas('daily_reports', [
            'user_id' => $this->user->id,
            'title' => '作業報告',
            'status' => 'submitted',
        ]);

        $this->postJson('/api/daily-reports', [
            'reported_at' => '',
            'title' => '',
            'content' => '',
            'status' => 'invalid',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['reported_at', 'title', 'content', 'status']);
    }

    public function test_only_the_owner_can_update_or_delete_a_report(): void
    {
        $report = $this->createReport($this->user, 1);
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)
            ->getJson("/api/daily-reports/{$report->id}")
            ->assertForbidden();

        $this->putJson("/api/daily-reports/{$report->id}", [
            'reported_at' => '2026-10-27',
            'title' => '不正な変更',
            'content' => '他のユーザーによる変更です。',
            'status' => 'submitted',
        ])->assertForbidden();

        $this->deleteJson("/api/daily-reports/{$report->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('daily_reports', [
            'id' => $report->id,
            'user_id' => $this->user->id,
            'title' => '日報 1',
        ]);
    }

    public function test_owner_can_update_and_delete_a_report(): void
    {
        $report = $this->createReport($this->user, 1);

        $this->putJson("/api/daily-reports/{$report->id}", [
            'reported_at' => '2026-10-28',
            'title' => '更新後',
            'content' => '内容を正常に更新しました。',
            'status' => 'submitted',
        ])->assertOk()
            ->assertJsonPath('data.title', '更新後');

        $this->deleteJson("/api/daily-reports/{$report->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('daily_reports', ['id' => $report->id]);
    }

    public function test_daily_report_api_requires_authentication(): void
    {
        $this->app['auth']->forgetGuards();

        $this->getJson('/api/daily-reports')
            ->assertUnauthorized();
    }

    private function createReport(User $user, int $number): DailyReport
    {
        return DailyReport::create([
            'user_id' => $user->id,
            'reported_at' => sprintf('2026-10-%02d', $number),
            'title' => "日報 {$number}",
            'content' => 'テスト内容を記録しました。',
            'status' => 'submitted',
        ]);
    }
}

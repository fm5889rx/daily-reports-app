<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_displays_daily_reports_and_create_link(): void
    {
        $dailyReport = DailyReport::create([
            'reported_at' => '2026-10-27',
            'title' => '作業報告',
            'content' => 'テスト内容を記録しました。',
            'status' => 'submitted',
        ]);

        $response = $this->get(route('daily-reports.index'));

        $response->assertOk()
            ->assertSee('作業報告')
            ->assertSee('テスト内容を記録しました。')
            ->assertSee('提出済')
            ->assertSee(route('daily-reports.create'))
            ->assertSee(route('daily-reports.edit', $dailyReport));
    }

    public function test_create_form_has_status_options(): void
    {
        $response = $this->get(route('daily-reports.create'));

        $response->assertOk()
            ->assertSee('min-h-screen max-w-2xl flex-col justify-center', false)
            ->assertSee('<select', false)
            ->assertSee('id="status"', false)
            ->assertSee('name="status"', false)
            ->assertSee('<option value="draft"', false)
            ->assertSee('>下書き</option>', false)
            ->assertSee('<option value="submitted"', false)
            ->assertSee('>提出済</option>', false);
    }

    public function test_store_creates_daily_report_and_redirects_to_index(): void
    {
        $response = $this->post(route('daily-reports.store'), [
            'reported_at' => '2026-10-27',
            'title' => '作業報告',
            'content' => '本日の作業内容を報告します。',
            'status' => 'submitted',
        ]);

        $response->assertRedirect(route('daily-reports.index'))
            ->assertSessionHas('success', '日報を作成しました。');

        $this->assertDatabaseHas('daily_reports', [
            'reported_at' => '2026-10-27',
            'title' => '作業報告',
            'status' => 'submitted',
        ]);
    }

    public function test_store_returns_field_validation_errors_in_red_on_the_form(): void
    {
        $response = $this->from(route('daily-reports.create'))
            ->post(route('daily-reports.store'), [
                'reported_at' => '',
                'title' => '',
                'content' => '',
                'status' => 'invalid',
            ]);

        $response->assertRedirect(route('daily-reports.create'))
            ->assertSessionHasErrors(['reported_at', 'title', 'content', 'status']);

        $formResponse = $this->get(route('daily-reports.create'));
        $formResponse->assertOk()
            ->assertSee('class="error text-sm text-red-600"', false)
            ->assertSee('報告日は必須です。')
            ->assertSee('タイトルは必須です。')
            ->assertSee('内容は必須です。')
            ->assertSee('ステータスは「下書き」または「提出済」を選択してください。');
    }

    public function test_edit_form_shows_existing_values_and_status_options(): void
    {
        $dailyReport = DailyReport::create([
            'reported_at' => '2026-10-27',
            'title' => '編集対象',
            'content' => '編集前の内容を記録しています。',
            'status' => 'submitted',
        ]);

        $response = $this->get(route('daily-reports.edit', $dailyReport));

        $response->assertOk()
            ->assertSee('value="編集対象"', false)
            ->assertSee('<option value="submitted" selected', false)
            ->assertSee('>提出済</option>', false);
    }

    public function test_update_changes_daily_report(): void
    {
        $dailyReport = DailyReport::create([
            'reported_at' => '2026-10-26',
            'title' => '変更前',
            'content' => '変更前の内容を記録しています。',
            'status' => 'draft',
        ]);

        $response = $this->put(route('daily-reports.update', $dailyReport), [
            'reported_at' => '2026-10-27',
            'title' => '変更後',
            'content' => '変更後の内容を記録しています。',
            'status' => 'submitted',
        ]);

        $response->assertRedirect(route('daily-reports.index'))
            ->assertSessionHas('success', '日報を更新しました。');

        $this->assertDatabaseHas('daily_reports', [
            'id' => $dailyReport->id,
            'title' => '変更後',
            'content' => '変更後の内容を記録しています。',
            'status' => 'submitted',
        ]);
    }

    public function test_destroy_deletes_daily_report(): void
    {
        $dailyReport = DailyReport::create([
            'reported_at' => '2026-10-27',
            'title' => '削除対象',
            'content' => '削除される日報の内容です。',
            'status' => 'draft',
        ]);

        $response = $this->delete(route('daily-reports.destroy', $dailyReport));

        $response->assertRedirect(route('daily-reports.index'))
            ->assertSessionHas('success', '日報を削除しました。');
        $this->assertDatabaseMissing('daily_reports', ['id' => $dailyReport->id]);
    }

    public function test_index_displays_confirmation_for_deleting_a_daily_report(): void
    {
        DailyReport::create([
            'reported_at' => '2026-10-27',
            'title' => '削除対象',
            'content' => '削除される日報の内容です。',
            'status' => 'draft',
        ]);

        $response = $this->get(route('daily-reports.index'));

        $response->assertOk()
            ->assertSee(
                'onsubmit="return confirm(\'この日報を削除してもよろしいですか？\')"',
                false
            );
    }
}

<?php

namespace Tests\Unit;

use App\Http\Requests\StoreDailyReportRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class DailyReportTest extends TestCase
{
    public function test_daily_report_validation_rules_accept_valid_data(): void
    {
        foreach (['draft', 'submitted'] as $status) {
            $validator = Validator::make([
                'reported_at' => '2026-10-27',
                'title' => 'Daily update',
                'content' => 'Completed the assigned tasks.',
                'status' => $status,
            ], (new StoreDailyReportRequest)->rules());

            $this->assertTrue($validator->passes());
        }
    }

    public function test_daily_report_validation_rules_reject_invalid_data(): void
    {
        $rules = (new StoreDailyReportRequest)->rules();
        $validData = [
            'reported_at' => '2026-10-27',
            'title' => 'Daily update',
            'content' => 'Completed the assigned tasks.',
            'status' => 'draft',
        ];

        $invalidCases = [
            ['reported_at' => 'not-a-date', 'expected_error' => 'reported_at'],
            ['title' => str_repeat('a', 51), 'expected_error' => 'title'],
            ['title' => 123, 'expected_error' => 'title'],
            ['content' => 'Too short', 'expected_error' => 'content'],
            ['content' => 123, 'expected_error' => 'content'],
            ['status' => 'archived', 'expected_error' => 'status'],
            ['status' => 123, 'expected_error' => 'status'],
        ];

        foreach ($invalidCases as $invalidCase) {
            $expectedError = $invalidCase['expected_error'];
            unset($invalidCase['expected_error']);

            $validator = Validator::make(
                array_merge($validData, $invalidCase),
                $rules
            );

            $this->assertArrayHasKey(
                $expectedError,
                $validator->errors()->messages()
            );
        }
    }

    public function test_daily_report_validation_rules_require_all_fields(): void
    {
        $validator = Validator::make(
            [],
            (new StoreDailyReportRequest)->rules()
        );

        $this->assertEqualsCanonicalizing(
            ['reported_at', 'title', 'content', 'status'],
            array_keys($validator->errors()->messages())
        );
    }

    public function test_default_validation_messages_are_in_japanese(): void
    {
        $validator = Validator::make([], ['title' => ['required']]);

        $this->assertSame(
            'タイトルは必須項目です。',
            $validator->errors()->first('title')
        );
    }
}

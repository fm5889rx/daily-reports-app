<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDailyReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reported_at' => ['required', 'date'],
            'title' => ['required', 'string', 'max:50'],
            'content' => ['required', 'string', 'min:10'],
            'status' => ['required', 'string', 'in:draft,submitted'],
        ];
    }

    public function messages(): array
    {
        return [
            'reported_at.required' => '報告日は必須です。',
            'reported_at.date' => '報告日は正しい日付で入力してください。',
            'title.required' => 'タイトルは必須です。',
            'title.string' => 'タイトルは文字列で入力してください。',
            'title.max' => 'タイトルは50文字以内で入力してください。',
            'content.required' => '内容は必須です。',
            'content.string' => '内容は文字列で入力してください。',
            'content.min' => '内容は10文字以上で入力してください。',
            'status.required' => 'ステータスは必須です。',
            'status.string' => 'ステータスを選択してください。',
            'status.in' => 'ステータスは「下書き」または「提出済」を選択してください。',
        ];
    }
}

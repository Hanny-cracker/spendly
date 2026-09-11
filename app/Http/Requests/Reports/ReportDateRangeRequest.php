<?php

namespace App\Http\Requests\Reports;

use App\Data\Report\DateRangeData;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReportDateRangeRequest extends FormRequest
{
    public function dateRange(): DateRangeData
    {
        return new DateRangeData(
            userId: (int) $this->user()->id,
            startDate: Carbon::parse($this->validated('start_date'))->startOfDay(),
            endDate: Carbon::parse($this->validated('end_date'))->endOfDay(),
        );
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date', 'before_or_equal:end_date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }
}

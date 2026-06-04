<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MeetingUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $meeting = $this->route('meeting');

        if (!$meeting) {
            return false;
        }

        $workspace = $this->user()?->activeWorkspace;

        $isCreator = $meeting->user_id === $this->user()?->id;
        $isAdmin = $workspace && $workspace->members()
            ->where('user_id', $this->user()->id)
            ->where('role', 'admin')
            ->exists();

        return $isCreator || $isAdmin;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
            'attendees' => ['nullable', 'array'],
            'attendees.*' => ['string', 'max:255'],
            'bilan' => ['nullable', 'array'],
            'bilan.*' => ['nullable', 'string', 'max:5000'],
            'recommendations' => ['nullable', 'array'],
            'recommendations.*' => ['nullable', 'string', 'max:5000'],
            'actions' => ['nullable', 'array'],
            'actions.*.text' => ['nullable', 'string', 'max:255'],
            'actions.*.assignee_id' => ['nullable', 'exists:users,id'],
            'actions.*.deadline' => ['nullable', 'date'],
        ];
    }
}

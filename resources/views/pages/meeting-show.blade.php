<x-app-layout>
@section('page-title', 'Compte rendu')
<livewire:meetings.meeting-editor :meetingId="$meeting->id" />
</x-app-layout>
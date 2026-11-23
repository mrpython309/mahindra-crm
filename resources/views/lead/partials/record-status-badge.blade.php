@php
    $recordStatus = $lead->status ?? 'active';
@endphp
@if($recordStatus === 'closed')
    <span class="badge bg-label-danger" title="Record status">Closed</span>
@elseif($recordStatus === 'active')
    <span class="badge bg-label-success" title="Record status">Active</span>
@else
    <span class="badge bg-label-warning text-dark" title="Record status">Draft</span>
@endif

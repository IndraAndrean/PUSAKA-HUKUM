@php
    $statusMap = [
        'berlaku' => ['label' => 'Berlaku', 'class' => 'text-bg-success'],
        'diubah' => ['label' => 'Diubah', 'class' => 'text-bg-warning'],
        'dicabut' => ['label' => 'Dicabut', 'class' => 'text-bg-danger'],
        'tidak_berlaku' => ['label' => 'Tidak Berlaku', 'class' => 'text-bg-light'],
    ];
    $statusInfo = $statusMap[$document->document_status] ?? ['label' => ucfirst(str_replace('_', ' ', $document->document_status)), 'class' => 'text-bg-light'];
@endphp
<span class="badge {{ $statusInfo['class'] }}">{{ $statusInfo['label'] }}</span>

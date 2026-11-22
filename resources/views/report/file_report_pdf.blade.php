<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>File Report PDF</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
        }

        .date-range {
            text-align: center;
            margin-bottom: 15px;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
        }

        th {
            background-color: #f2f2f2;
            text-align: left;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>

<h2>File Report</h2>

<div class="date-range">
    From {{ \Carbon\Carbon::parse($from)->format('d M Y') }}
    to {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Official File No</th>
            <th>File Name</th>
            <th>Subject</th>
            <th>Priority</th>
            <th>Category</th>
            <th>Sub Category</th>
            <th>Branch</th>
            <th>Department</th>
            <th>Employee</th>
            <th>Created By</th>
            <th>Start Date</th>
            <th>Due Date</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
    @forelse($files as $index => $file)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>{{ $file->official_file_number }}</td>
            <td>{{ $file->file_name }}</td>
            <td>{{ $file->subject ?? '-' }}</td>
            <td>{{ ucfirst($file->priority) }}</td>
            <td>{{ $file->category->name ?? '-' }}</td>
            <td>{{ $file->subCategory->name ?? '-' }}</td>
            <td>{{ $file->branch->name ?? '-' }}</td>
            <td>{{ $file->department->name ?? '-' }}</td>
            <td>{{ $file->employee->name ?? '-' }}</td>
            <td>{{ $file->createdBy->name ?? '-' }}</td>
            <td>{{ \Carbon\Carbon::parse($file->start_date)->format('d M Y') }}</td>
            <td>
                {{ $file->due_date ? \Carbon\Carbon::parse($file->due_date)->format('d M Y') : '-' }}
            </td>
            <td>{{ $file->status == 1 ? 'Closed' : 'Open' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="14" class="text-center">No files found for selected date range</td>
        </tr>
    @endforelse
    </tbody>
</table>

</body>
</html>

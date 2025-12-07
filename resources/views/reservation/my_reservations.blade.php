<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>حجوزاتي</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="direction:rtl; text-align:right; font-family:Cairo, sans-serif;">

<div class="container py-4">

    <h3 class="mb-4">حجوزاتي</h3>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>المركّب</th>
                <th>التاريخ</th>
                <th>الوقت</th>
                <th>التكلفة</th>
                <th>حالة الحجز</th>
                <th>حالة الدفع</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reservations as $r)
            <tr>
                <td>{{ $r->complex->nom }}</td>
                <td>{{ $r->date_reservation }}</td>
                <td>{{ $r->heure_debut }} - {{ $r->heure_fin }}</td>
                <td>{{ $r->montant }} دج</td>
                <td>{{ $r->statut }}</td>
                <td>{{ $r->paiement->statut ?? 'En attente' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>

</body>
</html>

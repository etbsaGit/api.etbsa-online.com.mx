<!DOCTYPE html>
<html>
<head>
    <title>{{$survey->title}}</title>
    <style>
        /* Estilos generales */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }
        .container {
            width: 90%;
            margin: 0 auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2, h3 {
            color: #007bff;
            margin-bottom: 10px;
        }
        h1 {
            font-size: 24px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        h2 {
            font-size: 20px;
            margin-top: 0;
        }
        h3 {
            font-size: 18px;
            margin-top: 20px;
        }
        p {
            font-size: 16px;
            line-height: 1.6;
        }
        .rating {
            font-weight: bold;
            color: #007bff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
            font-size: 16px;
        }
        td {
            font-size: 14px;
        }
        .correct {
            background-color: #d4edda; /* Verde claro */
        }
        .incorrect {
            background-color: #f8d7da; /* Rojo claro */
        }
        .footer {
            margin-top: 20px;
            padding: 10px;
            text-align: center;
            font-size: 14px;
            border-top: 2px solid #007bff;
            color: #007bff;
        }
        /* Estilo para saltos de página */
        @media print {
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Evaluado: {{ $employee->nombreCompleto }}</h1>
        <h2>Detalle de la Evaluación</h2>
        <p><strong>Nombre de la evaluación:</strong> {{ $survey->title }}</p>
        <p><strong>Descripción de la evaluación:</strong> {{ $survey->description }}</p>
        <p><strong>Calificación:</strong> <span class="rating">{{ number_format($averageRating, 2) }}%</span></p>

        <!-- Aquí puedes agregar una clase de salto de página si necesitas un salto de página manual -->
        <div class="page-break"></div>

        <h3>Preguntas:</h3>
        <table>
            <thead>
                <tr>
                    <th>Pregunta</th>
                    <th>Tu Respuesta</th>
                    <th>Comentarios</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($survey->question as $question)
                    @php
                        // Determinar la clase según la calificación
                        $rowClass = $question->answers->rating == 1 ? 'correct' : 'incorrect';
                    @endphp
                    <tr class="{{ $rowClass }}">
                        <td>{{ $question->question }}</td>
                        <td>{{ $question->answers->answer }}</td>
                        <td>{{ $question->answers->comments }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            Evaluación Generada por Sistema de Evaluaciones ETBSA
        </div>
    </div>
</body>
</html>

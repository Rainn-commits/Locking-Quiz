<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Mengubah isi file CSV soal guru menjadi array soal berisi string.
 *
 * Format CSV (baris pertama = header, urutan kolom bebas, huruf besar/kecil bebas):
 *   soal,a,b,c,d,e,jawaban
 *   "Berapa 2 + 2?",3,4,5,6,,B
 *
 * - Pemisah boleh koma (,) atau titik koma (;) — Excel Indonesia memakai titik koma.
 * - Kolom a..e = pilihan jawaban, minimal 2 yang terisi. Kolom kosong dilewati.
 * - jawaban = huruf pilihan yang benar (A–E).
 */
class QuestionCsvParser
{
    private const OPTION_KEYS = ['a', 'b', 'c', 'd', 'e'];

    /**
     * @return array{questions: array<int, array{prompt: string, options: array<int, array{text: string, is_correct: bool}>}>, errors: array<int, string>}
     */
    public function parseFile(string $path): array
    {
        $content = file_get_contents($path);

        if ($content === false) {
            throw new InvalidArgumentException('File CSV tidak dapat dibaca.');
        }

        return $this->parseString($content);
    }

    /**
     * @return array{questions: array<int, array{prompt: string, options: array<int, array{text: string, is_correct: bool}>}>, errors: array<int, string>}
     */
    public function parseString(string $content): array
    {
        $content = $this->toUtf8($content);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content); // buang BOM dari Excel
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        $lines = $this->readRows($content, $this->detectDelimiter($content));

        if ($lines === []) {
            throw new InvalidArgumentException('File CSV kosong.');
        }

        $header = array_map(fn ($h) => strtolower($this->toText($h)), array_shift($lines));

        if (! in_array('soal', $header, true) || ! in_array('jawaban', $header, true)) {
            throw new InvalidArgumentException('Header CSV harus berisi kolom "soal", "a", "b", ..., dan "jawaban".');
        }

        $questions = [];
        $errors = [];

        foreach ($lines as $i => $row) {
            $lineNumber = $i + 2; // +1 header, +1 karena mulai dari 1

            $row = array_map(fn ($cell) => $this->toText($cell), $row);

            if (implode('', $row) === '') {
                continue; // baris kosong
            }

            $data = [];
            foreach ($header as $col => $name) {
                $data[$name] = $row[$col] ?? '';
            }

            $prompt = $data['soal'];
            $answer = strtolower($data['jawaban']);

            if ($prompt === '') {
                $errors[] = "Baris {$lineNumber}: kolom soal kosong.";
                continue;
            }

            $options = [];
            $answerFound = false;

            foreach (self::OPTION_KEYS as $key) {
                $text = $data[$key] ?? '';
                if ($text === '') {
                    continue;
                }

                $isCorrect = $key === $answer;
                $answerFound = $answerFound || $isCorrect;

                $options[] = ['text' => mb_substr($text, 0, 255), 'is_correct' => $isCorrect];
            }

            if (count($options) < 2) {
                $errors[] = "Baris {$lineNumber}: minimal dua pilihan jawaban.";
                continue;
            }

            if (! $answerFound) {
                $errors[] = "Baris {$lineNumber}: jawaban \"{$data['jawaban']}\" tidak cocok dengan pilihan yang terisi.";
                continue;
            }

            $questions[] = ['prompt' => $prompt, 'options' => $options];
        }

        return ['questions' => $questions, 'errors' => $errors];
    }

    /**
     * Mengubah nilai sel apa pun menjadi string yang rapi.
     */
    public function toText(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }

        if (is_float($value)) {
            // 0.1 + 0.2 => "0.3", 5.0 => "5"
            return rtrim(rtrim(number_format($value, 10, '.', ''), '0'), '.');
        }

        $text = (string) $value;
        $text = str_replace("\u{00A0}", ' ', $text);   // non-breaking space dari Excel
        $text = preg_replace('/[ \t]+/u', ' ', $text);  // spasi ganda jadi satu

        return trim($text);
    }

    private function detectDelimiter(string $content): string
    {
        $firstLine = strtok($content, "\n") ?: '';

        return substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
    }

    /**
     * @return array<int, array<int, string|null>>
     */
    private function readRows(string $content, string $delimiter): array
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $content);
        rewind($stream);

        $rows = [];
        while (($row = fgetcsv($stream, 0, $delimiter, '"', '')) !== false) {
            if ($row === [null]) {
                continue;
            }
            $rows[] = $row;
        }
        fclose($stream);

        return $rows;
    }

    private function toUtf8(string $content): string
    {
        if (mb_check_encoding($content, 'UTF-8')) {
            return $content;
        }

        // CSV dari Excel Windows sering berformat Windows-1252
        return mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
    }
}

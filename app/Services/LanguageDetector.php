<?php

namespace App\Services;

/**
 * Lightweight EN/FIL/Taglish detector. Not a real NLP model — good enough
 * for picking a default reply language. Users can always override via the
 * EN/FIL toggle in the chatbot header.
 */
class LanguageDetector
{
    /** High-signal Filipino markers — enough that one of them is decisive. */
    private const STRONG_FIL = [
        'ang', 'ng', 'mga', 'ano', 'alin', 'ilan', 'saan', 'nasaan', 'paano', 'kailan', 'bakit', 'sino',
        'gusto', 'ayaw', 'pwede', 'puwede', 'meron', 'mayroon', 'wala', 'hindi', 'oo', 'opo', 'po',
        'ako', 'ikaw', 'kami', 'kayo', 'namin', 'natin', 'ninyo', 'nila',
        'ito', 'iyan', 'iyon', 'dito', 'diyan', 'doon', 'naman', 'lang',
        'masarap', 'maganda', 'magandang', 'malapit', 'malayo', 'mahal', 'mura',
        'kumusta', 'kamusta', 'salamat', 'pasensya', 'magkano', 'presyo', 'sulit',
        'tipid', 'gastos', 'pasyal', 'pasyalan', 'punta', 'pumunta',
        'kakain', 'hanap', 'hanapin', 'tulong', 'patulong', 'rekomenda', 'irekomenda',
        'oras', 'bukas', 'sarado', 'tayo', 'ko',
    ];

    /** Taglish signals — common English words that appear inside Filipino sentences. */
    private const WEAK_FIL = [
        'ba', 'po', 'din', 'rin', 'daw', 'raw', 'nga', 'kasi',
    ];

    public function detect(string $text): string
    {
        $text = mb_strtolower($text);
        $tokens = preg_split('/[\s\p{P}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        if (empty($tokens)) return 'en';

        $strongHits = 0;
        $weakHits   = 0;

        foreach ($tokens as $t) {
            if (in_array($t, self::STRONG_FIL, true)) $strongHits++;
            elseif (in_array($t, self::WEAK_FIL, true)) $weakHits++;
        }

        // One strong marker is enough; two weak markers also flips to FIL.
        if ($strongHits >= 1 || $weakHits >= 2) {
            return 'fil';
        }

        return 'en';
    }

    /** Fallback when detection is ambiguous: respect the user's stored preference. */
    public function detectWithPreference(string $text, ?string $userPreference = null): string
    {
        $detected = $this->detect($text);

        // If user has a preference and the message is short/ambiguous, respect it.
        $tokenCount = count(preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY));
        if ($tokenCount < 4 && $userPreference) {
            return $userPreference;
        }

        return $detected;
    }
}

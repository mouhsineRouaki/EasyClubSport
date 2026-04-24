<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class SeederImageStorage
{
    private const DEFAULT_PROFILE = 'seeders/defaults/profil-default.svg';
    private const DEFAULT_CLUB = 'seeders/defaults/club-default.svg';
    private const DEFAULT_TEAM = 'seeders/defaults/equipe-default.svg';
    private const DEFAULT_ANNOUNCEMENT = 'seeders/defaults/annonce-default.svg';

    public function userPhoto(string $fullName, string $role = 'joueur'): string
    {
        $colors = [
            'president' => ['0f766e', 'ffffff'],
            'coach' => ['1d4ed8', 'ffffff'],
            'joueur' => ['7c3aed', 'ffffff'],
            'admin' => ['111827', 'ffffff'],
        ];

        [$background, $foreground] = $colors[$role] ?? $colors['joueur'];

        $url = 'https://ui-avatars.com/api/?name=' . rawurlencode($fullName)
            . '&background=' . $background
            . '&color=' . $foreground
            . '&size=256&bold=true&format=png';

        return $this->storeRemoteImage(
            directory: 'seeders/profils',
            fileName: $fullName . '-' . $role,
            url: $url,
            fallbackPath: self::DEFAULT_PROFILE,
        );
    }

    public function clubLogo(string $clubName): string
    {
        [$background, $foreground] = $this->pickColorPair($clubName);

        return $this->storeRemoteImage(
            directory: 'seeders/clubs',
            fileName: $clubName,
            url: $this->placeholderUrl(512, 512, $clubName, $background, $foreground),
            fallbackPath: self::DEFAULT_CLUB,
        );
    }

    public function equipeLogo(string $teamName): string
    {
        [$background, $foreground] = $this->pickColorPair($teamName . '-team');

        return $this->storeRemoteImage(
            directory: 'seeders/equipes',
            fileName: $teamName,
            url: $this->placeholderUrl(512, 512, $teamName, $background, $foreground),
            fallbackPath: self::DEFAULT_TEAM,
        );
    }

    public function annonceImage(string $title): string
    {
        [$background, $foreground] = $this->pickColorPair($title . '-announcement');

        return $this->storeRemoteImage(
            directory: 'seeders/annonces',
            fileName: $title,
            url: $this->placeholderUrl(1280, 720, $title, $background, $foreground),
            fallbackPath: self::DEFAULT_ANNOUNCEMENT,
        );
    }

    public function storeRemoteImage(string $directory, string $fileName, ?string $url, string $fallbackPath): string
    {
        if (blank($url)) {
            return $fallbackPath;
        }

        $safeFileName = Str::slug($fileName);

        if ($safeFileName === '') {
            $safeFileName = 'image-' . Str::random(8);
        }

        try {
            $response = Http::timeout(10)
                ->accept('image/*')
                ->get($url);

            if (! $response->successful()) {
                return $fallbackPath;
            }

            $contentType = strtolower((string) $response->header('Content-Type'));

            if (! str_starts_with($contentType, 'image/')) {
                return $fallbackPath;
            }

            $extension = $this->resolveExtension($contentType, $url);
            $path = $directory . '/' . $safeFileName . '.' . $extension;

            Storage::disk('public')->put($path, $response->body());

            return $path;
        } catch (Throwable) {
            return $fallbackPath;
        }
    }

    private function placeholderUrl(int $width, int $height, string $label, string $background, string $foreground): string
    {
        $text = rawurlencode(Str::limit($label, 30, ''));

        return "https://placehold.co/{$width}x{$height}/{$background}/{$foreground}.png?text={$text}";
    }

    private function pickColorPair(string $seed): array
    {
        $palette = [
            ['0f766e', 'ffffff'],
            ['1d4ed8', 'ffffff'],
            ['b45309', 'ffffff'],
            ['7c3aed', 'ffffff'],
            ['be123c', 'ffffff'],
            ['166534', 'ffffff'],
        ];

        $index = hexdec(substr(md5($seed), 0, 6)) % count($palette);

        return $palette[$index];
    }

    private function resolveExtension(string $contentType, string $url): string
    {
        if (str_contains($contentType, 'svg')) {
            return 'svg';
        }

        if (str_contains($contentType, 'jpeg') || str_contains($contentType, 'jpg')) {
            return 'jpg';
        }

        if (str_contains($contentType, 'webp')) {
            return 'webp';
        }

        $path = parse_url($url, PHP_URL_PATH);
        $extension = pathinfo((string) $path, PATHINFO_EXTENSION);

        if (in_array($extension, ['png', 'jpg', 'jpeg', 'webp', 'svg'], true)) {
            return $extension === 'jpeg' ? 'jpg' : $extension;
        }

        return 'png';
    }
}

<?php

namespace Tests\Unit;

use App\Support\Geo;
use App\Support\Phone;
use App\Support\Text;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SupportTest extends TestCase
{
    public static function phones(): array
    {
        return [
            'spaces'      => ['6 90 00 00 00', '+237690000000'],
            'dashes'      => ['690-000-000', '+237690000000'],
            'with +237'   => ['+237 690000000', '+237690000000'],
            'with 00237'  => ['00237690000000', '+237690000000'],
            'with 237'    => ['237690000000', '+237690000000'],
            'landline'    => ['222 22 22 22', '+237222222222'],
            'too short'   => ['69000000', null],
            'wrong start' => ['590000000', null],
            'foreign'     => ['+33612345678', null],
            'empty'       => ['', null],
        ];
    }

    #[Test]
    #[DataProvider('phones')]
    public function it_normalizes_cameroon_phone_numbers(string $raw, ?string $expected): void
    {
        $this->assertSame($expected, Phone::normalize($raw));
    }

    #[Test]
    public function the_same_name_written_differently_gives_the_same_key(): void
    {
        $key = Text::nameKey('TEMGOUA Anantia');

        $this->assertSame('anantia temgoua', $key);
        $this->assertSame($key, Text::nameKey('anantia   temgoua'));
        $this->assertSame($key, Text::nameKey('Témgoua Anantia'));
        $this->assertSame($key, Text::nameKey('TEMGOUA-ANANTIA'));
    }

    #[Test]
    public function it_masks_names(): void
    {
        $this->assertSame('A****** T******', Text::maskName('Anantia Temgoua'));
    }

    #[Test]
    public function it_computes_distances(): void
    {
        // Yaoundé → Douala is about 200 km in a straight line
        $km = Geo::distanceKm(3.8480, 11.5021, 4.0511, 9.7679);

        $this->assertEqualsWithDelta(195, $km, 10);
    }
}

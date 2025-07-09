<?php

namespace App\Domain\Financial\Enums;

enum GTUCodeEnum: string
{
    public const THRESHOLD_50_000 = 50_000.00;

    case GTU_01 = 'GTU_01';
    case GTU_02 = 'GTU_02';
    case GTU_03 = 'GTU_03';
    case GTU_04 = 'GTU_04';
    case GTU_05 = 'GTU_05';
    case GTU_06 = 'GTU_06';
    case GTU_07 = 'GTU_07';
    case GTU_08 = 'GTU_08';
    case GTU_09 = 'GTU_09';
    case GTU_10 = 'GTU_10';
    case GTU_11 = 'GTU_11';
    case GTU_12 = 'GTU_12';
    case GTU_13 = 'GTU_13';

    public function getOfficialName(): string
    {
        return match ($this) {
            self::GTU_01 => 'Napoje alkoholowe',
            self::GTU_02 => 'Towary związane z tytoniem',
            self::GTU_03 => 'Paliwa silnikowe',
            self::GTU_04 => 'Pojazdy samochodowe',
            self::GTU_05 => 'Urządzenia elektroniczne',
            self::GTU_06 => 'Części i akcesoria do pojazdów',
            self::GTU_07 => 'Towary o wartości przekraczającej 50 000 PLN',
            self::GTU_08 => 'Metale szlachetne',
            self::GTU_09 => 'Lekarstwa i wyroby medyczne',
            self::GTU_10 => 'Budynki, budowle i grunty',
            self::GTU_11 => 'Świadczenia gazowe',
            self::GTU_12 => 'Świadczenia energii elektrycznej',
            self::GTU_13 => 'Świadczenia telekomunikacyjne',
        };
    }

    public function hasAmountThreshold(): bool
    {
        return self::GTU_07 === $this;
    }

    public function getAmountThreshold(): ?float
    {
        return match ($this) {
            self::GTU_07 => self::THRESHOLD_50_000,
            default      => null,
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::GTU_01 => 'Napoje alkoholowe - piwo, wino, napoje fermentowane i wyroby pośrednie',
            self::GTU_02 => 'Towary związane z tytoniem - papierosy, cygaretka, tytoń, płyn do e-papierosów',
            self::GTU_03 => 'Paliwa silnikowe - benzyna, olej napędowy, gaz LPG',
            self::GTU_04 => 'Pojazdy samochodowe - samochody osobowe, dostawcze, motocykle',
            self::GTU_05 => 'Urządzenia elektroniczne - telefony, komputery, tablety',
            self::GTU_06 => 'Części i akcesoria do pojazdów - opony, akumulatory, części zamienne',
            self::GTU_07 => 'Towary o wartości przekraczającej 50 000 złotych za sztukę lub komplet',
            self::GTU_08 => 'Metale szlachetne - złoto, srebro, platyna oraz wyroby z tych metali',
            self::GTU_09 => 'Lekarstwa i wyroby medyczne - leki, sprzęt medyczny',
            self::GTU_10 => 'Budynki, budowle i grunty - nieruchomości oraz prawa do nich',
            self::GTU_11 => 'Świadczenia gazowe - dostawa gazu ziemnego',
            self::GTU_12 => 'Świadczenia energii elektrycznej - dostawa energii elektrycznej',
            self::GTU_13 => 'Świadczenia telekomunikacyjne - usługi telekomunikacyjne',
        };
    }
}

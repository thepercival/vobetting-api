<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 5-7-19
 * Time: 11:18
 */

namespace FCToernooi;

use Voetbal\Sport\Custom as SportCustom;
use Voetbal\Sport\ScoreConfig as SportScoreConfig;
use Voetbal\Sport;

class TranslationService {

    const language = 'nl';

    public function getSportName( string $language, int $customId): string {
        switch ($customId) {
            case SportCustom::Badminton: { return 'badminton'; }
            case SportCustom::Basketball: { return 'basketbal'; }
            case SportCustom::Darts: { return 'darten'; }
            case SportCustom::ESports: { return 'e-sporten'; }
            case SportCustom::Hockey: { return 'hockey'; }
            case SportCustom::Korfball: { return 'korfbal'; }
            case SportCustom::Chess: { return 'schaken'; }
            case SportCustom::Squash: { return 'squash'; }
            case SportCustom::TableTennis:
            {
                return 'tafeltennis';
            }
            case SportCustom::Tennis:
            {
                return 'tennis';
            }
            case SportCustom::Football:
            {
                return 'voetbal';
            }
            case SportCustom::Volleyball:
            {
                return 'volleybal';
            }
            case SportCustom::Baseball:
            {
                return 'honkbal';
            }
        }
        return '';
    }

    public function getScoreNameSingular(SportScoreConfig $sportScoreConfig): string
    {
        $customId = $sportScoreConfig->getSport()->getCustomId();
        if ($sportScoreConfig->isFirst()) {
            return $this->getFirstScoreNameSingular($customId);
        } else {
            if ($sportScoreConfig->isLast()) {
                return $this->getLastScoreNameSingular($customId);
            }
        }
        return '';
    }

    protected function getFirstScoreNameSingular(int $customId): string
    {
        switch ($customId) {
            case SportCustom::Darts:
            {
                return 'leg';
            }
            case SportCustom::Tennis:
            {
                return 'game';
            }
            case SportCustom::Football:
            case SportCustom::Hockey:
            {
                return 'goal';
            }
        }
        return 'punt';
    }

    protected function getLastScoreNameSingular(int $customId): string
    {
        switch ($customId) {
            case SportCustom::Badminton:
            case SportCustom::Darts:
            case SportCustom::Squash:
            case SportCustom::TableTennis:
            case SportCustom::Tennis:
            case SportCustom::Volleyball:
            {
                return 'set';
            }
        }
        return '';
    }

    public function getScoreNamePlural(SportScoreConfig $sportScoreConfig): string
    {
        $customId = $sportScoreConfig->getSport()->getCustomId();
        if ($sportScoreConfig->isFirst()) {
            return $this->getFirstScoreNamePlural($customId);
        } else {
            if ($sportScoreConfig->isLast()) {
                return $this->getLastScoreNamePlural($customId);
            }
        }
        return '';
    }

    protected function getFirstScoreNamePlural(int $customId): string
    {
        switch ($customId) {
            case SportCustom::Darts:
            {
                return 'legs';
            }
            case SportCustom::Tennis:
            {
                return 'games';
            }
            case SportCustom::Football:
            case SportCustom::Hockey:
            {
                return 'goals';
            }
        }
        return 'punten';
    }

    protected function getLastScoreNamePlural(int $customId): string
    {
        switch ($customId) {
            case SportCustom::Badminton:
            case SportCustom::Darts:
            case SportCustom::Squash:
            case SportCustom::TableTennis:
            case SportCustom::Tennis:
            case SportCustom::Volleyball:
            {
                return 'sets';
            }
        }
        return '';
    }

    public function getScoreDirection(string $language, int $direction): string
    {
        switch ($direction) {
            case SportScoreConfig::UPWARDS:
            {
                return 'naar';
            }
            case SportScoreConfig::DOWNWARDS:
            {
                return 'vanaf';
            }
        }
        return '';
    }

    public function getFieldNameSingular(string $language, Sport $sport): string
    {
        $customId = $sport->getCustomId();
        switch ($customId) {
            case SportCustom::Badminton:
            {
                return 'veld';
            }
            case SportCustom::Basketball:
            {
                return 'veld';
            }
            case SportCustom::Darts:
            {
                return 'bord';
            }
            case SportCustom::ESports:
            {
                return 'veld';
            }
            case SportCustom::Hockey:
            {
                return 'veld';
            }
            case SportCustom::Korfball:
            {
                return 'veld';
            }
            case SportCustom::Chess:
            {
                return 'bord';
            }
            case SportCustom::Squash:
            {
                return 'baan'; }
            case SportCustom::TableTennis: { return 'tafel'; }
            case SportCustom::Tennis: { return 'veld'; }
            case SportCustom::Football:
            {
                return 'veld';
            }
            case SportCustom::Volleyball:
            {
                return 'veld';
            }
            case SportCustom::Baseball:
            {
                return 'veld'; }
        }
        return '';
    }

    public function getFieldNamePlural(string $language, Sport $sport): string
    {
        $customId = $sport->getCustomId();
        switch ($customId) {
            case SportCustom::Badminton:
            {
                return 'velden';
            }
            case SportCustom::Basketball:
            {
                return 'velden';
            }
            case SportCustom::Darts:
            {
                return 'borden';
            }
            case SportCustom::ESports:
            {
                return 'velden';
            }
            case SportCustom::Hockey:
            {
                return 'velden';
            }
            case SportCustom::Korfball:
            {
                return 'velden';
            }
            case SportCustom::Chess:
            {
                return 'borden';
            }
            case SportCustom::Squash:
            {
                return 'banen'; }
            case SportCustom::TableTennis: { return 'tafels'; }
            case SportCustom::Tennis: { return 'velden'; }
            case SportCustom::Football:
            {
                return 'velden';
            }
            case SportCustom::Volleyball:
            {
                return 'velden';
            }
            case SportCustom::Baseball:
            {
                return 'velden'; }
        }
        return '';
    }
}

<?php

namespace PokerHand;

class PokerHand
{
    const CARD_ORDER = [ '14', '13', '12', '11', '10', '9', '8', '7', '6', '5', '4', '3', '2' ];
    const ROYALTY_TO_INT = [
      'a' => '14',
      'k' => '13',
      'q' => '12',
      'j' => '11',
    ];

    public $pair_count;
    public $three_count;
    public $four_count;
    public $is_straight;
    public $straight_index;

    public function __construct($hand)
    {
        $this->parseHand($hand);
        $this->countPairs();
        $this->isHandStraight();
    }

    public function getRank(): string
    {
        if ($this->isHandFlush()) {
            if ($this->straight_index === 0) {
                return 'Royal Flush';
            } elseif ($this->is_straight) {
                return 'Straight Flush';
            } else {
                return 'Flush';
            }
        } elseif ($this->is_straight) {
            return 'Straight';
        } elseif ($this->four_count) {
            return 'Four of a Kind';
        } elseif ($this->pair_count && $this->three_count) {
            return 'Full House';
        } elseif ($this->three_count) {
            return 'Three of a Kind';
        } elseif ($this->pair_count === 2) {
            return 'Two Pair';
        } elseif ($this->pair_count === 1) {
            return 'One Pair';
        } else {
            return 'High Card';
        }
    }

    public function parseHand($hand): void
    {
        $cards = [];
        $hand = explode(' ', $hand);

        foreach ($hand as $card) {
            $length = strlen($card) - 1;
            $suit = $card[strlen($card) - 1];
            $card = substr($card, 0, $length);
            $card = $this->convertRoyaltyToInt($card);
            $cards[] = [
                'card' => $card,
                'suit' => $suit
            ];
        }
        array_multisort($cards, SORT_DESC);
        $this->cards = $cards;
    }

    public function convertRoyaltyToInt($card): string
    {
        $card = strtolower($card);
        if(isset(self::ROYALTY_TO_INT[$card])) {
            return self::ROYALTY_TO_INT[$card];
        } else {
            return $card;
        }
    }

    public function isHandFlush(): bool
    {
        $flush_suit = $this->suitString()[0];
        foreach ($this->suitColumn() as $suit) {
            if ($suit !== $flush_suit) {
                return false;
            }
        }
        return true;
    }

    public function isHandStraight()
    {
        $card_order_string = implode('', self::CARD_ORDER);
        $this->straight_index = strpos($card_order_string, $this->cardString());
        $this->is_straight = (bool) $this->straight_index;
    }

    public function findPairs(): array
    {
        $pairs = [];

        foreach ($this->cardColumn() as $card) {
            if (!isset($pairs[$card])) {
                $pairs[$card] = 1;
            } else {
                $pairs[$card]++;
            }
        }

        return $pairs;
    }

    public function countPairs(): void
    {
        $pairs = $this->findPairs();
        $this->pair_count = 0;
        $this->three_count = 0;
        $this->four_count = 0;

        foreach ($pairs as $pair) {
            if ($pair === 2) {
                $this->pair_count++;
            } elseif ($pair === 3) {
                $this->three_count++;
            } elseif ($pair === 4) {
                $this->four_count++;
            }
        }
    }

    public function cardColumn(): array
    {
        return array_column($this->cards, 'card');
    }
    

    public function suitColumn(): array
    {
        return array_column($this->cards, 'suit');
    }
    
    public function suitString(): string
    {
        return implode($this->suitColumn());
    }

    public function cardString(): string
    {
        return implode($this->cardColumn());
    }
}

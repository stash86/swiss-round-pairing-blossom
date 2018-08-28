<?php

class Pairings
{
    private $lowestScoreWithoutBye = -1;
    private $highest_points = 0;
    private $indexOfBye = -1;
    public $pairing = [];

    public function __construct($players)
    {
        // $highest_points = 0;
        for ($i = 0; $i < count($players); $i++) {
            if ($players[$i]['id'] == 'BYE') {
                $this->indexOfBye = $i;
                continue;
            }
            $this->highest_points = max($this->highest_points, $players[$i]['points']);
            if (!in_array('BYE', $players[$i]['opponents'])) {
                if ($this->lowestScoreWithoutBye < 0) {
                    $this->lowestScoreWithoutBye = $players[$i]['points'];
                } else {
                    $this->lowestScoreWithoutBye = min($this->lowestScoreWithoutBye, $players[$i]['points']);
                }
            }
        }
        //In a weird case where all players have had at least 1 bye
        $this->lowestScoreWithoutBye = max($this->lowestScoreWithoutBye, 0);

        if ($this->indexOfBye > -1) {
            $players[$this->indexOfBye]['points'] = $this->lowestScoreWithoutBye - 3;
        }

        $ws = $this->weights($players);
        $mweight = new MaxWeightMatching($ws);
        $this->pairing = $mweight->main();
        // assert(-1 not in $ps);
        // return $ps;
    }

    public function weights($players)
    {
        $ws = [];
        for ($i = 0; $i < count($players); $i++) {
            for ($j = 0; $j < count($players); $j++) {
                if ($i == $j) {
                    continue;
                }
                $new_data = [$i, $j, $this->weight($this->highest_points, $players[$i], $players[$j])];
                array_push($ws, $new_data);
            }
        }

        return $ws;
    }

    public function weight($highest_points, $p1, $p2)
    {
        $w = 0;

        // A pairing where the participants have not played each other as many times as they have played at least one other participant outscore all pairings where the participants have played the most times.
        // This will stave off re-pairs and second byes for as long as possible, and then re-re-pairs and third byes, and so on …
        // $counter = count($p1['opponents']);
        if (!in_array($p2['id'], $p1['opponents']) //haven't played each other
            && ($p2['id'] != 'BYE' || $p1['points'] <= $this->lowestScoreWithoutBye)
            && ($p1['id'] != 'BYE' || $p2['points'] <= $this->lowestScoreWithoutBye)
        ) {
            $w += $this->quality($highest_points, $highest_points) + 1;
        }
        // if len(counter) > 0 and counter.get(p2['id'], sys.maxsize) < max(counter.values()):

        // Determine a score for the quality of this pairing based on the points of the higher scoring participant of the two (importance) and how close the two participant's records are.
        $best = max($p1['points'], $p2['points']);
        $worst = min($p1['points'], $p2['points']);
        $spread = $best - $worst;
        $closeness = $highest_points - $spread;
        $importance = $best;
        $w += $this->quality($importance, $closeness);

        return $w;
    }

    public function quality($importance, $closeness)
    {
        // We add one to these values to avoid sometimes multiplying by zero and losing information.
        return pow($importance + 1, 2) * pow($closeness + 1, 2);
    }
}

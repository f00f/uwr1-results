<?php
class Uwr1resultsRanking {
    public $rnk;
    private $resolveH2H = false;

    public $head2headTeams = array();
    public $head2headTeamIDs = array();

    /* Creates the ranking, accumulating all points and goals.
     * @param mixed $results all match results to be considered
     */
    public function &Uwr1resultsRanking(&$results) {
        $this->resolveH2H = ! (1 == @$_GET['nodv']);

        $this->rnk = array();

        foreach ($results as $f) {
            // add all teams to the ranking
            if ($f->t_b_name && $f->t_w_name) {
                $this->rnk[ $f->t_b_ID ]['id'] = $f->t_b_ID;
                $this->rnk[ $f->t_b_ID ]['name'] = $f->t_b_name;
                $this->rnk[ $f->t_w_ID ]['id'] = $f->t_w_ID;
                $this->rnk[ $f->t_w_ID ]['name'] = $f->t_w_name;
            }
            if (!$f->result_ID) { continue; } // don't count fixtures that don't have results
            if ($f->fixture_friendly) {
                // don't take friendly games into account for ranking
                $this->rnk[ $f->t_b_ID ]['friendlyMatchesPlayed']++;
                $this->rnk[ $f->t_w_ID ]['friendlyMatchesPlayed']++;
                continue;
            }

            $this->rnk[ $f->t_b_ID ]['id'] = $f->t_b_ID;
            $this->rnk[ $f->t_b_ID ]['name'] = $f->t_b_name;
            $this->rnk[ $f->t_b_ID ]['goalsPos'] += $f->result_goals_b;
            $this->rnk[ $f->t_b_ID ]['goalsNeg'] += $f->result_goals_w;
            $this->rnk[ $f->t_b_ID ]['pointsPos'] += $f->result_points_b;
            $this->rnk[ $f->t_b_ID ]['pointsNeg'] += $f->result_points_w;
            $this->rnk[ $f->t_b_ID ]['matchesPlayed']++;
            $this->rnk[ $f->t_b_ID ]['playedAgainst'][$f->t_w_ID] = true;

            $this->rnk[ $f->t_w_ID ]['id'] = $f->t_w_ID;
            $this->rnk[ $f->t_w_ID ]['name'] = $f->t_w_name;
            $this->rnk[ $f->t_w_ID ]['goalsPos'] += $f->result_goals_w;
            $this->rnk[ $f->t_w_ID ]['goalsNeg'] += $f->result_goals_b;
            $this->rnk[ $f->t_w_ID ]['pointsPos'] += $f->result_points_w;
            $this->rnk[ $f->t_w_ID ]['pointsNeg'] += $f->result_points_b;
            $this->rnk[ $f->t_w_ID ]['matchesPlayed']++;
            $this->rnk[ $f->t_w_ID ]['playedAgainst'][$f->t_b_ID] = true;
        }

        foreach ($this->rnk as $id => $team) {
            $this->rnk[ $id ]['goalsDiff']  = $team['goalsPos']  - $team['goalsNeg'];
            $this->rnk[ $id ]['pointsDiff'] = $team['pointsPos'] - $team['pointsNeg'];
            $this->rnk[ $id ]['head2head'] = false;
            if (!$this->rnk[ $id ]['matchesPlayed']) {
                $this->rnk[ $id ]['matchesPlayed'] = 0;
                $this->rnk[ $id ]['pointsPos']     = '&mdash;';
                $this->rnk[ $id ]['goalsPos']      = '&ndash;';
                $this->rnk[ $id ]['goalsNeg']      = '&ndash;';
                $this->rnk[ $id ]['goalsDiff']     = '&mdash;';
            }
        }
    }

    /* Sorts the ranking.
     * @param bool $resolveH2H do resolve head 2 head situations
     */
    public function sort($resolveH2H = false) {
        uasort($this->rnk, array($this, 'compareTeams')); // usort, uasort, uksort

        $this->resolveH2H = $resolveH2H;
        if ($this->usesResolveH2H()) {
            //print '<hr />Using DV<br />';
            $this->resolveHead2HeadSituations();
            //print '<hr />';
        }
    }

    public function usesResolveH2H() {
        return $this->resolveH2H;
    }

    /* Info about the team at a given rank.
     * @param int $num rank of the team
     */
    public function getRank($num) {
        // TODO: implement ...
    }

    /* Swaps two teams in the ranking.
     * @param int $num1 rank of the one team
     * @param int $num2 rank of the other team
     */
    public function swapRanks($num1, $num2) {
        // TODO: implement ...
    }

    /* Alters order of a portion of the ranking.
     * @param array $ordered_ids
     * Restrictions/Notes:
     * - the reordering may only affect a portion of the ranking
     * - the affected region has to be contiguous in the ranking
     */
    public function alterRankingOrder($ordered_ids) {
        //print "<pre>new order\n";print_r($ordered_ids);print '</pre>';
        //print "<pre>old rnk\n";print_r(array_keys($this->rnk));print '</pre>';
        // find first ID contained in $ordered_ids
        $offset = 0;
        foreach ($this->rnk as $k => $rank) {
            if (in_array($k, $ordered_ids)) {
                break;
            }
            ++$offset;
        }
        // get parts before and after the chunk
        $len1 = $offset;
        $len2 = count($ordered_ids);
        $len3 = $this->numTeams() - $len1 - $len2;
        $before = array_slice($this->rnk, 0, $len1, true);
        $chunk = array_slice($this->rnk, $len1, $len2, true);
        $after = array_slice($this->rnk, $len1+$len2, $len3, true);
        // reorder $chunk
        $ordered_chunk = array();
        foreach ($ordered_ids as $id) {
            $ordered_chunk[$id] = $chunk[$id];
        }
        unset($chunk);
        // combine three parts
        // three options:
        // - +-operator
        // - array_splice (which does not preserve numerical keys!)
        // - array_merge might corrupt numerical keys
        $this->rnk = $before + $ordered_chunk + $after;
        //print "<pre>new rnk\n";print_r(array_keys($this->rnk));print '</pre>';
        //$this->rnk = array_splice($this->rnk, $len1, $len2, $chunk);
        //$this->rnk = array_merge($before, $chunk, $after);
    }

    public function numTeams() {
        return count($this->rnk);
    }

    public function numberOfHead2HeadSituations() {
        return count($this->head2headTeamIDs);
    }

    public function hasHead2HeadSituations() {
        return $this->numberOfHead2HeadSituations() > 0;
    }

    public function isHead2HeadResolved() {
        return !$this->hasHead2HeadSituations();
    }

    /* Finds and resolves comparison of teams with equal points.
     * Called by $this->sort()
     * Manipulates $this->rnk
     *
     * If the comparison cannot be decided, the ranking is done using data from all matches.
     * That means $this->rnk will not be altered by this function.
     *
     * When can a head2head comparison be undecided?
     * - if there is an undefined state (i.e. one team has fewer games in this comparison)
     * - if teams are still equal (i.e. there is still a head2head situation, *and* goals bring no resolution)
     *
     * Cannot decide all cases: for some the result is unspecified.
     * E.g. when three teams have same points, but one of them has not played any of the other two.
     *      The direct comparison is then undecided, probably even undecidable.
     *
     * @testcase http://uwr1.test/ergebnisse/liga/1-bundesliga-nord
     * @testcase http://uwr1.test/ergebnisse/liga/2-bundesliga-nord
     * @testcase http://uwr1.test/ergebnisse/liga/1-bundesliga-sued
     * @testcase http://uwr1.test/ergebnisse/liga/2-bundesliga-sued
     */
    private function resolveHead2HeadSituations() {
        foreach ($this->head2headTeamIDs as $pts => $ids) {
            //print 'resolving '.implode(', ', $this->head2headTeams[ $pts ]).'<br />';
            $h2h_resolved = true;

            $num_teams_involved = count($ids);
            $h2h_results =& Uwr1resultsModelResult::instance()->findByTeamIds($ids);
            //print '<pre>';print_r($h2h_results);print '</pre>';
            // also, if there are no matches in the comparison, it is undecided
            if (0 === count($h2h_results)) {
                $h2h_resolved = false;
                //print 'no matches in comparison -&gt; undecidable.<br />';
                $this->resetHead2HeadSituation($ids);
                continue; // check next h2h situation
            }
            $h2h_rnk = new Uwr1resultsRanking($h2h_results);
            if ($h2h_rnk->numTeams() != $num_teams_involved) {
                $h2h_resolved = false;
                //print 'H2H ranking incomplete -&gt; undecidable.<br />';
                $this->resetHead2HeadSituation($ids);
                continue; // check next h2h situation
            }
            $h2h_rnk->sort(false);

            /* falls es immer noch ein head2head gibt
             *   dann gibt es noch punktgleiche teams
             *   dann muss man TorDiff und Tore anschauen.
             *   (sollte schon richtig sortiert sein)
             * falls dann immernoch unentschieden,
             *   dann kann dieser DV nicht entschieden werden
             *   dann wird nach den Werten *aller* Spiele gewertet
             *   (also das vorherige Ranking nicht verändert)
             */

            /* Check #1: is there a team with fewer matches in the comparison than the others?
             */
            if ($h2h_resolved) {
                $matches_played = null;
                foreach ($h2h_rnk->rnk as $rank) {
                    if (null === $matches_played) {
                        $matches_played = $rank['matchesPlayed'];
                    } elseif ($rank['matchesPlayed'] != $matches_played) {
                        $h2h_resolved = false;
                        //print 'inequal num. of matches -&gt; undecidable.<br />';
                        $this->resetHead2HeadSituation($ids);
                        break;
                    }
                }
            }

            /* Check #2: if there is still a h2h situation, can it be decided by the goals?
             *
             * Still h2h means: there are teams with same number of points within the comparison.
             * If there are also teams with different number of points, they are already sorted correctly.
             * If all teams in all h2h situations have equal goal diff. and pos goals, the situation is undecided.
             */
            if ($h2h_resolved && $h2h_rnk->hasHead2HeadSituations()) {
                foreach ($h2h_rnk->head2headTeamIDs as $pts => $team_ids) {
                    $goals_diff = null;
                    $pos_goals = null;
                    $all_equal = true;
                    foreach ($team_ids as $team_id) {
                        if (null === $goals_diff) {
                            $goals_diff = $h2h_rnk->rnk[ $team_id ]['goalsDiff'];
                            $pos_goals = $h2h_rnk->rnk[ $team_id ]['goalsPos'];
                        } else {
                            if ($goals_diff != $h2h_rnk->rnk[ $team_id ]['goalsDiff']) {
                                $all_equal = false;
                                break;
                            }
                            if ($pos_goals != $h2h_rnk->rnk[ $team_id ]['goalsPos']) {
                                $all_equal = false;
                                break;
                            }
                        }
                    }
                    if ($all_equal) {
                        //print 'all teams equal -&gt; undecidable.<br />';
                        $h2h_resolved = false;
                        $this->resetHead2HeadSituation($ids);
                        break;
                    }
                }
            }

            if ($h2h_resolved) {
                //print 'success. <b>Will update order of ranking.</b><br />';
                //print '<pre>';print_r($h2h_results);print '</pre>';
                //print '<pre>';print_r($h2h_rnk);print '</pre>';
                /*
                print 'Ranking:<br />';
                $i = 0;
                foreach ($h2h_rnk->rnk as $rank) {
                    print ++$i.'. '.$rank['name'].'<br />';
                }
                */
                $order_correct = $this->checkIfOrderIsCorrect($h2h_rnk);
                if ($order_correct) {
                    //print 'Prev. order was already correct<br />';
                } else {
                    $this->alterRankingOrder(array_keys($h2h_rnk->rnk));
                }
            } else {
                //print 'H2H is still undecided. Will NOT update order of ranking.<br />';
                //print '<pre>';print_r($h2h_rnk);print '</pre>';
            }
            // in each of the above cases the h2h situation is resolved.
            $this->resetHead2HeadSituation($ids);
        }
    }

    /* Check if the team ID's appear in the same order in both, the sub-ranking and $this->rnk.
     *
     */
    private function checkIfOrderIsCorrect($sub_ranking) {
        $ordered_ids = array_keys($sub_ranking->rnk);
        $next_id = 0;
        foreach ($this->rnk as $k => $rank) {
            if ($k == $ordered_ids[$next_id]) {
                ++$next_id;
            }
        }
        return ($next_id == count($ordered_ids));
    }

    private function resetHead2HeadSituation($ids) {
        foreach ($ids as $id) {
            //print $this->rnk[$id]['name'].' is no longer in H2H situation<br />';
            $this->rnk[$id]['head2head'] = false;
        }
    }

    private function compareTeams( &$a, &$b ) {
        // -1 : a < b : a before b
        //  0 : a = b : a equal  b
        // +1 : a > b : a after  b

        // one team played friendly matches only = worse (ausser Konkurrenz)
        if ($a['friendlyMatchesPlayed'] && ! $a['matchesPlayed'] && $b['matchesPlayed']) {
            return 1;
        }
        if ($b['friendlyMatchesPlayed'] && ! $b['matchesPlayed'] && $a['matchesPlayed']) {
            return -1;
        }

        // more pointsPos = better
        if ($a['pointsPos'] <> $b['pointsPos']) {
            return ($a['pointsPos'] > $b['pointsPos']) ? -1 : 1;
        }

        else {
            //print '<!-- DV: '.print_r($a['playedAgainst'], true).' vs. '.print_r($b, true).' -->';
            if ($a['matchesPlayed'] && $b['matchesPlayed']) {
                $a['head2head'] = $b['head2head'] = true;
                $this->head2headTeams[ $a['pointsPos'] ][$a['id']] = $a['name'];
                $this->head2headTeams[ $a['pointsPos'] ][$b['id']] = $b['name'];
                $this->head2headTeamIDs[ $a['pointsPos'] ][$a['id']] = $a['id'];
                $this->head2headTeamIDs[ $a['pointsPos'] ][$b['id']] = $b['id'];
            }
        }

        // equal pointsPos => higher goalsDiff = better
        if ($a['goalsDiff'] <> $b['goalsDiff']) {
            return ($a['goalsDiff'] > $b['goalsDiff']) ? -1 : 1;
        }

        // equal goalsDiff => more goalsPos = better
        if ($a['goalsPos'] <> $b['goalsPos']) {
            return ($a['goalsPos'] > $b['goalsPos']) ? -1 : 1;
        }

        // Los

        return 0; // considered equal
    }
} // Uwr1resultsRanking

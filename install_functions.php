<?php

/*********************************************************************************
 * Tidbit is a data generation tool for the SugarCRM application developed by
 * SugarCRM, Inc. Copyright (C) 2004-2016 SugarCRM Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by SugarCRM".
 ********************************************************************************/

function loggedQuery($query)
{
    if (!empty($GLOBALS['queryFP'])) {
        fwrite($GLOBALS['queryFP'], $query . "\n");
    }
    return $GLOBALS['db']->query($query, true, "INSERT QUERY FAILED");
}

/**
 * This function streamlines the query execution by performing all of the following:
 * 1. Attempt to insert by chunks, if the DB supports it.  So far only mysql does.
 * 2. Handling the query as a head and a body, dealing with chunks or no chunks.
 * 3. Writing to a log file.
 * @param $head
 * @param $values
 */
function processQueries($head, $values)
{
    if ($GLOBALS['sugar_config']['dbconfig']['db_type'] == 'mysql') {
        $chunks = array_chunk($values, 20, false);
    } else {
        $chunks = array_chunk($values, 1, false);
    }

    foreach ($chunks as $chunk) {
        $query = $head . implode(', ', $chunk);
        $result = loggedQuery($query);
    }
}

/**
 * Given an array return random array elements from the array
 *
 * @param array $array
 * @param int $num
 * @return array
 */
function get_random_array($array, $num)
{
    $rand = array_rand($array, $num);
    $result = array();

    for ($i = 0; $i < $num; $i++) {
        $result[$i] = $array[$rand[$i]];
    }
    return $result;
}

/**
 * generate_team_set
 * Helper function to recursively create team sets
 *
 * @param $primary string The primary team
 * @param $teams string The teams to use
 */
function generate_team_set($primary, $teams)
{
    if (!in_array($primary, $teams)) {
        array_push($teams, $primary);
    }
    $teams = array_reverse($teams);
    $team_count = count($teams);
    for ($i = 0; $i < $team_count; $i++) {
        /** @var TeamSet $teamSet */
        $teamSet = BeanFactory::getBean('TeamSets');
        $teamSet->addTeams($teams);
        array_pop($teams);
    }
}

function generate_full_teamset($set, $teams)
{
    $team_count = count($teams);
    for ($i = 0; $i < $team_count; $i++) {
        $teamset = new TeamSet();
        $teamset->addTeams(array_unique(array_merge($set, array($teams[$i]))));
    }
}

/**
 * @param string $dir
 */
function clearCsvDir($dir)
{
    $fileToDelete = glob($dir . '/*csv');
    foreach($fileToDelete as $file){
        if(is_file($file)) {
            unlink($file);
        }
    }
}

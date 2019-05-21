<?php declare(strict_types = 1);
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * MAIN Model
 *
 * PLEASE NOTE THAT I HAVE INTENTIONALLY USED IN ONE METHOD A QUERY STRING AND NOT PDO
 * THE PURPOSE OF THIS IS TO SHOW MY ORACLE SQL SKILLS
 * THE FOLLOWING FUNCTIONS WILL USE PDO.
 *
 * Primary utility model for the Application
 */
class Main_m extends CI_Model {
	/**
	 * Get Report List For Logged/Registered Users.
	 *
	 * THIS ONE USES A QUERY STRING SO YOU CAN EVALUATE MY ORACLE SQL SKILLS.
	 *
	 * @param string $userLevel   Restrict search to public users (default: TRUE).
	 * @param array  $userSchemas The Schemas available for this user.
	 * @return array
	 * @throws Exception On error loading table list.
	 */
	public function getReportsList(string $userLevel, array $userSchemas = array()) : array {
		$query = "
			SELECT DISTINCT
				RGC.GROUP_NAME_TEXT,
				RD.REPORT_DEFINITION_KEY,
				RD.REPORT_NAME,
				RD.REPORT_DESC,
				CASE WHEN RS.SCHEDULE_BEGIN_DATE IS NOT NULL
					THEN 'Y'
					ELSE 'N'
				END AS SCHEDULED_IND,
				RGC.DISPLAY_ORDER_NBR,
				RD.PUBLICLY_ACCESSIBLE_IND
			FROM SCHEMA.TABLE1 RD
			INNER JOIN SCHEMA.TABLE2 RDGL
				ON RDGL.COL1 = RD.REPORT_DEFINITION_KEY
			INNER JOIN SCHEMA.TABLE2 RGC
				ON RDGL.COL2 = RGC.REPORT_GROUP_KEY
			LEFT OUTER JOIN SCHEMA.TABLE3 RS
				ON RS.COL3 = RD.REPORT_DEFINITION_KEY
			WHERE ";
		if (count($userSchemas) > 0) {
			$query .= " RD.DATABASE_SCHEMA_NAME IN ('".implode("','", $userSchemas)."') AND ";
		}
		if ($userLevel === 'OPTION1' || $userLevel === 'OPTION2') {
			$query .= "(RD.PUBLICLY_ACCESSIBLE_IND = 'Y') AND ";
		}
		$query .= "(RD.DELETE_TS IS NULL OR RD.DELETE_TS > CURRENT_DATE)
			AND (RGC.END_DATE IS NULL OR RGC.END_DATE > CURRENT_DATE)
			ORDER BY RGC.DISPLAY_ORDER_NBR, UPPER(RGC.GROUP_NAME_TEXT), UPPER(RD.REPORT_NAME)
		";
		$results = $this->db->query($query)->result_array();
		if (!is_array($results)) {
			throw new Exception("Error loading tables list");
		}
		$reportList = array();
		foreach ($results as $key => $record) {
			$groupName = $record['GROUP_NAME_TEXT'];
			$reportList[$groupName][] = $record;
		}
		return $reportList;
	}

	/**
	 * Get Current Period Date.
	 *
	 * @param string $format Date format.
	 * @return string RECON Period Date in specified format.
	 */
	public function getCurrentPeriod(string $format = 'MM/YYYY') : string {
		$query = $this->db
			->select('
				TO_CHAR(PERIOD_DATE,\'' . $format . '\') AS PERIOD_DATE,
    		')
			->from('SCHEMA.TABLE')
			->where('PERIOD_STATUS_IND', 'AVAILABLE')
			->get();
		$periodDate = 'N/A';
		if ($result = $query->row()) {
			$periodDate = $result->PERIOD_DATE;
		}
		return $periodDate;
	}

	/**
	 * Change Status.
	 *
	 * Receive an array with content like the following below:
	 * Array (
	 * 		[field1] => ?
	 * 		[field2] => ?
	 * 		[field3] => ?
	 * 		[field4] => ?
	 * )
	 *
	 * @param array $data The data collected to update.
	 * @return array Returns Success/Error messages after updating fields.
	 */
	public function changeStatus(array $data) : array {
		if (!empty($data)) {
			$this->db->set('fieldA', $data['field1']);
			$this->db->set('fieldB', $data['field2']);
			$this->db->set('fieldC', $data['field3']);
			$this->db->set('MODIFY_TS', 'SYSDATE', FALSE);
			$this->db->where_in('fieldD', $data['field4'], FALSE);
			$this->db->update('SCHEMA.TABLE');
			log_message('DEBUG', $this->db->last_query());  // Logging the query.
			$results = 'All Items have been updated.';
		} else {
			$results = 'No Items were updated';
		}
		return array(
			'status' => 'success',
			'message' => $results
		);
	}
}

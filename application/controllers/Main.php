<?php declare(strict_types = 1);
defined('BASEPATH') OR exit('No direct script access allowed');

use app\output\ExcelReport;
use app\schedule\Scheduler;

/**
 * Main Controller
 * Default controller for the Application.
 * Contains main page and utility endpoints.
 *
 * For the Code Sample, we are assuming there is a "library" folder with
 * the namespaces mentioned above.
 * Also, note we are using the CodeIgniter folder structure.
 *
 * These lines of code below are for the sole purpose of reference of sintax and work style.
 * There is no whole functionality working and not all methods are linked.
 *
 * @property Main_m $main
 */
class Main extends Another_Controller {
	/**
	 * Main Controller Constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->load->model('Main_m', 'main');
	}
	/**
	 * Application Home Page.
	 *
	 * @return void
	 */
	public function index() {
		log_message('INFO', __CLASS__ . '::' . __FUNCTION__);
		$this->data['publicReportsList'] = $this->main->getReportsList($this->data['userLevel'], $this->data['userSchemas']);
		$this->data['pageClass'] = 'home-page';
		$this->data['pageCss'] = 'static/css/main/main.css';
		$this->data['pageTitle'] = 'Application Title';
		$alertMessage = $this->session->flashdata('alertMessage');
		if ($alertMessage !== NULL) {
			$this->data['alertMessage'] = $alertMessage;
		}
		$this->data['pageView'] = 'main/main_v';
		$this->load->view('content_v', $this->data);
	}

	/**
	 * Get Users List.
	 *
	 * JSON service for user search. Using GET for convenience.
	 *
	 * @return CI_Output
	 */
	public function getUsersList() : CI_Output {
		log_message('INFO', __CLASS__ . '::' . __FUNCTION__);
		$searchName = $this->input->get('name', TRUE);
		$includeUser = $this->input->get('included');
		try {
			$users = $this->main->getDataForUserSearch($searchName);
			if ($includeUser !== 'true') {
				$key = array_search(array_column($users, 'userId'));
				if ($key !== FALSE) {
					unset($users[$key]);
					$users = array_values($users);
				}
			}
			$response = array(
				'status' => 'success',
				'users' => $users
			);
		} catch (Exception $e) {
			$response = array(
				'status' => 'error',
				'message' => $e.message
			);
		}
		echo json_encode($response);
	}

	/**
	 * Download Secure Files.
	 *
	 * @param string $fileCode The Code with Relative Path (Base64 Encoded).
	 * @return void
	 */
	public function downloadSecureFiles(string $fileCode) {
		$filename = base64_decode($fileCode);
		$link = SOME_CONSTANT . $filename;
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Cache-Control: max-age=0');
		ob_clean();
		flush();
		readfile($link);
		exit;
	}

	/**
	 * Manual Trigger
	 *
	 * Called by the user to start a CLI process manually via browser.
	 *
	 * @return CI_Output
	 */
	public function mstart() : CI_Output {
		log_message('INFO', __CLASS__ . '::' . __FUNCTION__);
		$execPath = PHP_PATH . ' ' . FCPATH . 'index.php main start';
		$this->_execProcess($execPath);
		return $this->output
			->set_content_type('application/json')
			->set_status_header(200)
			->set_output(json_encode(array(
				'status' => 'success'
			)));
	}

	/**
	 * Runs an scheduled process
	 *
	 * This method is called from CLI and will perform some tasks.
	 * Usefull to run CRON Jobs.
	 *
	 * @param string $clearSchedule If true the REPORT_SCHEDULE_LOG is truncated before running.
	 *                              Set to false if you only wish to restart a failed schedule run
	 *                              and you do not wish to rerun reports that were already successful.
	 *                              Default value is 'true', note it is a string, not a boolean.
	 * @param string $silentMode    Set to '--silent' to NOT send notifications.
	 * @return void
	 */
	public function start(string $clearSchedule = 'true', string $silentMode = '') {
		log_message('INFO', __CLASS__ . '::' . __FUNCTION__);
		if (!$this->input->is_cli_request()) {
			show_404();
		}
		set_time_limit(0); // Prevents server from timing out request.
		ini_set('memory_limit', '256M');
		$scheduler = new Scheduler();
		$scheduler->run($clearSchedule, $silentMode);
	}

	/**
	 * Execute a process
	 *
	 * This functionality is only intended for Linux. Disabled for Windows.
	 *
	 * @param string $execString Command-line execution string.
	 * @return mixed Boolean or Array Containing process resource and pipe streams
	 */
	protected function _execProcess(string $execString) {
		log_message('INFO', __CLASS__ . '::' . __FUNCTION__ . '::' . $execString);
		if (php_uname('s') == 'Windows NT') {
			return FALSE;
		}
		if (php_uname('s') == 'Linux') {
			$descriptorspec = array(
				0 => array('pipe', 'r'),  // The stdin is a pipe that the child will read from.
				1 => array('pipe', 'w'),  // The stdout is a pipe that the child will write to.
				2 => array('pipe', 'w')   // The stderr is a file to write to.
			);
			$proc = proc_open($execString . '  > /dev/null &', $descriptorspec, $pipes);
			if (is_resource($proc)) {
				log_message('INFO', 'Opened Process: ' . json_encode(proc_get_status($proc)));
			} else {
				log_message('ERROR', 'Could not create process.');
				return NULL;
			}
			// Return Process Resource and defined pipes.  Both required to keep track of the process.
			return array(
				'proc' => $proc,
				'pipes' => $pipes
			);
		} else {
			exec($execString);
			return TRUE;
		}
	}
}

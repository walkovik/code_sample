<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<!--[if lt IE 8]><html class="no-js oldIE" lang="en-US"><![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9" lang="en-US"><![endif]-->
<!--[if IE 9]><html class="no-js ie9" lang="en-US"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html lang="en-US" class="no-js not-ie"><!--<![endif]-->
	<head>
	    <?php $this->load->view('header_includes_v'); ?>
	    <title><?php echo $pageTitle ?></title>
	</head>
	<body class="<?php echo $pageClass ?>">
		<header role="banner">
			<?php $this->load->view('app_header_v'); ?>
			<?php $this->load->view('links_header_v'); ?>
		</header>
		<main role="main" aria-label="main section" id="main-content">
			<?php $this->load->view($pageView); ?>
		</main>
		<footer class="footer" role="contentinfo">
			<?php $this->load->view('app_footer_v'); ?>
		</footer>
		<?php $this->load->view('links_footer_v'); ?>
	</body>
</html>

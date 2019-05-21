<div class="container">
	<div class="row">
		<div class="col-sm-12">
			<div id="accordion" class="panel-group" role="tablist" aria-multiselectable="true">
<?php
	$i = 0;
	foreach ($publicReportsList as $groupName => $groupReports) {
?>
				<div class="panel panel-default">
					<div class="panel-heading" role="tab">
						<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo $i;?>"
							<?php echo (($i===0) ? 'aria-expanded="true"' : 'aria-expanded="false"');?>
							aria-controls="collapse<?php echo $i;?>"><?php echo $groupName; ?></a>
					</div>
					<div id="collapse<?php echo $i;?>" class="panel-body panel-collapse row report-list collapse <?php echo (($i===0) ? 'in') : '';?>" <?php (($i===0) ? 'aria-expanded="true" ' : 'aria-expanded="false" ');?>>
<?php foreach ($groupReports as $key => $report) : ?>
						<div class="col-md-6"><a href="<?php echo base_url(); ?>path/to/link/<?php echo $report['REPORT_DEFINITION_KEY']; ?>" title="<?php echo $report['REPORT_DESC'];?>"><?php echo $report['REPORT_NAME'];?></a></div>
<?php endforeach; ?>
					</div>
				</div>
<?php $i++; ?>
<?php } ?>
			</div>
		</div>
	</div>
</div>

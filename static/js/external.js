import AppConstants from 'AppConstants';
import $ from 'jquery';
export default class external {
	static previewReport(strDefinition, parentObj) {
		if ($('.preview-btn').hasClass('btn-default')) {
			$('.preview-btn').removeClass('btn-default');
			$('.preview-btn').addClass('btn-primary');
			$('.btn-reset').prop('disabled', true);
			$('.btn-reset').addClass('btn-disabled');
			$('#report-criteria').addClass('hidden');
			$('#report-definition').addClass('hidden');
			$('#report-preview').addClass('hidden');
			$('#report-loading').removeClass('hidden');
			$.ajax({
				url: AppConstants.BASE_URL + 'path/to/preview',
				data: {definition: JSON.stringify(strDefinition)},
				method: 'POST',
				dataType: 'json'
			})
			.done((json) => {
				if (json.results >= AppConstants.MAX_OUTPUT_PREVIEW_RESULTS) {
					$('#report-warning-limit').removeClass('hidden');
				}
				$('#report-loading').addClass('hidden');
				$('#report-preview').removeClass('hidden');
				parentObj.previewTable.destroy();
				$('#preview').empty();
				$.fn.dataTable.ext.type.detect.unshift(
					function (d) {
						let r = /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/;
						return r.test(d) ? 'date-us' : null;
					}
				);
				$.fn.dataTable.ext.type.order['date-us-pre'] = function (d) {
					let r = /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/;
					let a = d.match(r);
					return 'Date'+a[3]+''+a[1]+''+a[2];
				};
				parentObj.previewTable = $('#preview').DataTable({
					columns: json.columns,
					data: json.data,
					'searching': true,
					'pageLength': 50,
					'lengthMenu': [10,25,50,100],
					'order': [],
					'deferRender': true
				});
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				$('#report-preview').addClass('hidden');
				$('#report-loading').addClass('hidden');
				$('#report-definition').removeClass('hidden');
				$('#report-criteria').removeClass('hidden');
				alert('Error: ' + errorThrown);
			});
		} else {
			$('.btn-reset').prop('disabled', false);
			$('.btn-reset').removeClass('btn-disabled');
			$('.preview-btn').addClass('btn-default');
			$('.preview-btn').removeClass('btn-primary');
			$('#report-preview').addClass('hidden');
			$('#report-loading').addClass('hidden');
			$('#report-definition').removeClass('hidden');
			$('#report-criteria').removeClass('hidden');
			$('#report-warning-limit').addClass('hidden');
		}
	}
}

import AppConstants from 'AppConstants';
import _ from 'underscore';
import $ from 'jquery';
import external from 'external';
/*eslint no-unused-vars: ["error", { "varsIgnorePattern": "[bootstrap|datatables]"}] */
import datatables from 'datatables';
import bootstrap from 'bootstrap';

/**
 * Main JS file.
 *
 * Written using ECMASCRIPT6 (ES6) Standard.
 * When using gulp and a transpile tool (babel),
 * we can have a guarantee 100% browser compatible javascript output.
 * Please note the use of promises, fat arrow functions and const/let.
 */
class Main {
	constructor(args) {
		this.mode = args.mode;
		this.columnsTemplate = _.template($('#template-columns-list').html());
		this.registerPageEvents();
	}

	registerPageEvents() {
		$('#schema').change((event) => {
			const schema = $(event.currentTarget).val();
			if (schema === '[no-schema-selected]') {
				$('.report-btn-group .btn').attr('disabled', 'disabled');
				$('#collapseTables').collapse('hide');
			} else {
				this.getTableData(schema).then((schemaTables) => {
					this.populateTables(schemaTables);
				});
			}
		});

		// Populating tables from default schema.
		let schema = $('#schema').val();
		if (schema !== '[no-schema-selected]') {
			this.getTableData(schema).then((schemaTables) => {
				this.populateTables(schemaTables);
			});
		}

		this.previewTable = $('#preview').DataTable({
			'bSort': false,
			'aoColumns': [{
				bSearchable: false,
				bSortable: false
			}]
		});

		$('.preview-btn').click(() =>{
			external.previewReport(this.reportDefinition, this);
		});

		// Adding this prevents bootstrap from closing all other panels
		// when toggling a panel.
		$('a[data-toggle="collapse"]').on('click',(event) => {
			const objectID = $(event.currentTarget).attr('href');
			if($(objectID).hasClass('in')) {
				$(objectID).collapse('hide');
			} else {
				$(objectID).collapse('show');
			}
			return false;
		});
	}

	populateColumns() {
		const templateHTML = this.columnsTemplate({
			columns: this.reportDefinition.columns,
			conditions: Column.CONDITIONS,
		});
		$('#template-target-columns-list').html(templateHTML).promise().done(() => {
			$('#collapseColumns').collapse('show');
			this.registerColumnEvents();
		});
	}

	// Populate tables in dropdown.
	populateTables(schemaTables) {
		$('#table_name').empty();
		$('#table_name').append('<option value="[no-table-selected]">Select a Table</option>');
		$('#table_name').append('<option class="select-dash" disabled="disabled">--------------</option>');
		let sortedSchemaTables = _.sortBy(schemaTables, 'TABLE_SCREEN_NAME');
		$.each(sortedSchemaTables, (key, value) => {
			$('#table_name').append('<option value="' + value.KEY + '">' + value.TABLE_SCREEN_NAME + '</option>');
		});
		$('#collapseTables').collapse('show');
	}

	// Helper function to get tables for selected schema.
	getTableData(schema) {
		return new Promise((resolve) => {
			const svcUrl = AppConstants.BASE_URL + 'path/to/table/data/' + schema;
			$.getJSON(svcUrl, (response) => {
				if (response.status === 'error') {
					// eslint-disable-next-line no-console
					console.log('Error encountered calling definition/schema_tables service. Error Message = ' + response.message);
					resolve(null);
				} else {
					resolve(response.tables);
				}
			});
		});
	}
}
export function init(args) {
	new Main(args);
}

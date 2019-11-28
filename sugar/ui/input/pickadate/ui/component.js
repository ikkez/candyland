
/**
 * pickadate datepicker
 */
Vue.component('pickadate', {
	template:
		'<div class="uk-inline">'+
			'<input ref="input" class="uk-input uk-form-width-medium datepicker" :name="input_name" :data-value="input_value" :placeholder="input_placeholder">'+
		'</div>'
	,
	props: ['name','value','format','placeholder'],
	data: function() {
		return {
			input_name: this.name,
			input_value: this.value,
			input_placeholder: this.placeholder !== undefined ? this.placeholder : 'TT.MM.JJJJ',
			input: false,
		}
	},
	mounted: function () {
		$('.datepicker',this.$el).pickadate({
			min: new Date(),
			editable: false,
			format: 'dd.mm.yyyy',
			formatSubmit: 'yyyy-mm-dd',
			hiddenName: true,
			onSet: this.update
		});
	},
	methods: {
		update: function(date) {
			// console.log(date);
		},
	}
});
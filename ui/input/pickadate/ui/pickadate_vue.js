
/**
 * pickadate datepicker
 */
Vue.component('pickadate', {
	template:
		'<div class="uk-inline">'+
			'<input ref="input" class="uk-input uk-form-width-medium pickadate" :name="input_name" :data-value="input_value" :placeholder="input_placeholder">'+
		'</div>'
	,
	props: ['name','value','format','placeholder','min'],
	data: function() {
		return {
			input_name: this.name,
			input_value: this.value,
			input_placeholder: this.placeholder !== undefined ? this.placeholder : 'TT.MM.JJJJ',
			input: false,
			format: typeof this.format !== undefined ? this.format : 'dd.mm.yyyy',
			min: typeof this.min !== undefined ? this.min : false,
		}
	},
	mounted: function () {
		let opt= {
			editable: false,
			format: 'dd.mm.yyyy',
			formatSubmit: 'yyyy-mm-dd',
			hiddenName: true,
			onSet: this.update
		};
		if (this.min) {
			opt.min= new Date(this.min);
		}
		if (this.max) {
			opt.max= new Date(this.max);
		}
		$('.pickadate',this.$el).pickadate(opt);
	},
	methods: {
		update: function(date) {
			// console.log(date);
		},
	}
});
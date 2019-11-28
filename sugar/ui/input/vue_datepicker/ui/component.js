
Vue.component('datepicker', {
	template:
		'<vuejs-datepicker ref="input" @input="update($event)" :minimumView="input_minview" :maximumView="input_maxview" clear-button="true" clear-button-icon="fas fa-times" wrapper-class="uk-inline" input-class="uk-input uk-form-width-small" :name="input_name" :value="input_value" monday-first="true" :format="input_format" :placeholder="input_placeholder">'
	,
	components: {
		vuejsDatepicker
	},
	props: ['name','value','format','placeholder','monthOnly'],
	data: function() {
		return {
			input_name: this.name,
			input_value: this.value,
			input_format: this.format !== undefined ? this.format : 'dd.MM.yyyy',
			input_placeholder: this.placeholder !== undefined ? this.placeholder : 'TT.MM.JJJJ',
			input_minview: this.monthOnly ? 'month' : 'day',
			input_maxview: this.monthOnly ? 'month' : 'year',
			input: false,
		}
	},
	// mounted: function () {
	// 	this.input = UIkit.util.$('.uk-input',this.$el);
	// },
	methods: {
		update: function(date) {
			// 		if (date) {
			// 			this.input.value = date.toISOString();
			// 			this.input._value = date.toISOString();
			// 		}
		},
	}
});
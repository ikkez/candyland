
/**
 * pickadate datepicker
 */
Vue.component('pickadate', {
	template:
		'<input ref="input" class="uk-input uk-form-width-medium pickadate" :name="input_name" :data-value="input_value" :placeholder="input_placeholder">'
	,
	props: ['name','value','format','placeholder','minDate','maxDate','selectMonths','selectYears'],
	data: function() {
		return {
			input_el: null,
			picker: null,
			input_name: this.name,
			input_value: this.value,
			input_placeholder: this.placeholder !== undefined ? this.placeholder : 'TT.MM.JJJJ',
			input: false,
			format: typeof this.format !== undefined ? this.format : 'dd.mm.yyyy',
			minDate: typeof this.minDate !== undefined ? this.minDate : false,
			maxDate: typeof this.maxDate !== undefined ? this.maxDate : false,
			selectMonths: typeof this.selectMonths !== undefined ? this.selectMonths : false,
			selectYears: typeof this.selectYears !== undefined ? this.selectYears : false,
		}
	},
	mounted: function () {
		let opt = {
			editable: false,
			format: 'dd.mm.yyyy',
			formatSubmit: 'yyyy-mm-dd',
			hiddenName: true,
			onSet: this.update,
			onClose: this.blur,
			selectMonths: this.selectMonths,
			selectYears: this.selectYears,
		};
		if (this.minDate) {
			opt.min= new Date(this.minDate);
		}
		if (this.maxDate) {
			opt.max= new Date(this.maxDate);
		}
		this.input_el = $(this.$el).pickadate(opt);
		this.picker = this.input_el.pickadate('picker');
	},
	methods: {
		formatDate: function(date) {
			var d = new Date(date),
				month = '' + (d.getMonth() + 1),
				day = '' + d.getDate(),
				year = d.getFullYear();
			if (month.length < 2)
				month = '0' + month;
			if (day.length < 2)
				day = '0' + day;
			return [year, month, day].join('-');
		},
		update: function(context) {
			if (context.select) {
				let val = context.select;
				if (typeof val === "object")
					val = val.pick;
				let date = new Date(val);
				this.$emit('input', this.formatDate(date));
			} else
				this.$emit('input', '');
		},
		blur: function() {
			this.$emit('blur');
		}
	},
	watch: {
		minDate: function(newVal, oldVal) {
			if (newVal)
				newVal = new Date(newVal);
			this.picker.set('min', newVal);
		},
		maxDate: function(newVal, oldVal) {
			if (newVal)
				newVal = new Date(newVal);
			this.picker.set('max', newVal);
		},
		value: function (newVal, oldVal) {
			if (newVal && typeof oldVal === "undefined") {
				this.picker.set('select', newVal);
			}
		}
	}
});


/**
 * pickadate timepicker
 */
Vue.component('pickatime', {
	template:
		'<input ref="input" class="uk-input uk-form-width-medium pickadate timepicker" :name="input_name" :data-value="input_value" :placeholder="input_placeholder">'
	,
	props: ['name','value','format','placeholder','minTime','maxTime','interval'],
	data: function() {
		return {
			input_el: null,
			picker: null,
			input_name: this.name,
			input_value: this.value,
			interval: typeof this.interval !== undefined ? this.interval : 30,
			input_placeholder: this.placeholder !== undefined ? this.placeholder : '00:00',
			input: false,
			format: typeof this.format !== undefined ? this.format : 'h:i A',
			minTime: typeof this.minTime !== undefined ? this.minTime : false,
			maxTime: typeof this.maxTime !== undefined ? this.maxTime : false,
		}
	},
	mounted: function () {
		let opt = {
			editable: false,
			format: 'HH:i',
			formatSubmit: 'HHi',
			hiddenName: true,
			onSet: this.update,
			onClose: this.blur,
			interval: this.interval,
		};
		if (this.minTime) {
			opt.min= new Date(this.minTime);
		}
		if (this.maxTime) {
			opt.max= new Date(this.maxTime);
		}
		this.input_el = $(this.$el).pickatime(opt);
		this.picker = this.input_el.pickatime('picker')
	},
	methods: {
		update: function(context) {
			this.$emit('input', context.select);
		},
		blur: function() {
			this.$emit('blur');
		}
	},
	watch: {
		minDate: function(newVal, oldVal) {
			if (newVal)
				newVal = new Date(newVal);
			this.picker.set('min', newVal);
		},
		maxDate: function(newVal, oldVal) {
			if (newVal)
				newVal = new Date(newVal);
			this.picker.set('max', newVal);
		}
	}
});
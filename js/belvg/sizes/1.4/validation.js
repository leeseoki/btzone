Validation.addAllThese([
    ['validate-no-html-tags', 'HTML tags are not allowed', function(v) {
        return !/<(\/)?\w+/.test(v);
	}],
]);
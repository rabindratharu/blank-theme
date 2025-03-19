document.addEventListener("DOMContentLoaded", function () {
	let searchInput = document.querySelector("input.wp-block-search__input"); // Adjust selector if needed

	if (searchInput) {
		searchInput.addEventListener("focus", function () {
			this.setAttribute("data-placeholder", this.placeholder);
			this.placeholder = "";
		});

		searchInput.addEventListener("blur", function () {
			this.placeholder = this.getAttribute("data-placeholder");
		});
	}
});
(function () {
    var header = document.querySelector(".site-header");
    var toggle = document.querySelector("[data-nav-toggle]");
    if (toggle && header) {
        toggle.addEventListener("click", function () {
            header.classList.toggle("nav-open");
        });
    }

    var seatSelect = document.querySelector("[data-seat-count]");
    var passengerWrap = document.querySelector("[data-passenger-rows]");
    if (seatSelect && passengerWrap) {
        function syncRows() {
            var n = parseInt(seatSelect.value, 10) || 1;
            var rows = passengerWrap.querySelectorAll(".passenger-row");
            rows.forEach(function (row, i) {
                row.style.display = i < n ? "" : "none";
                row.querySelectorAll("input").forEach(function (inp) {
                    inp.required = i < n;
                });
            });
        }
        seatSelect.addEventListener("change", syncRows);
        syncRows();
    }
})();

document.addEventListener("DOMContentLoaded", () => {
    const statusDropdowns = document.querySelectorAll("select[name='status']");

    statusDropdowns.forEach(select => {
        select.addEventListener("change", function () {
            const confirmChange = confirm(`Are you sure you want to mark this shipment as '${this.value}'?`);
            if (!confirmChange) {
                this.value = this.getAttribute("data-original");
            }
        });
    });
});

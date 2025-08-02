console.log('hello');
function explist() {
    var form = document.getElementById("exptab");
    var tab = form.querySelector("select[name=tab]");
    tab.addEventListener("change", function() {
        console.log(`tab sel: ${this.value}`);
        form.submit();
    });
}

console.log('bye');

explist();

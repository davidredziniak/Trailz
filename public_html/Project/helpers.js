function flash(message = "", color = "info") {
    let flash = document.getElementById("flash");
    //create a div (or whatever wrapper we want)
    let outerDiv = document.createElement("div");
    outerDiv.className = "row justify-content-center";
    let innerDiv = document.createElement("div");

    //apply the CSS (these are bootstrap classes which we'll learn later)
    innerDiv.className = `alert alert-${color} mt-4 fade show`;
    innerDiv.setAttribute("role", "alert");
    //set the content
    innerDiv.innerText = message;

    let close = document.createElement("a");
    close.href = "#";
    close.className = "close";
    close.setAttribute("data-bs-dismiss", "alert");
    close.setAttribute("aria-label", "close");
    close.setAttribute("style", "text-decoration: none;float: right;");

    close.innerHTML = "&times;";
    innerDiv.appendChild(close);
    outerDiv.appendChild(innerDiv);

    //add the element to the DOM (if we don't it merely exists in memory)
    flash.appendChild(outerDiv);
}
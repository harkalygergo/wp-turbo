<h2>Documentation</h2>
<ul>
    <li>
        <b>Add "logged-in-user-[ID]" class to body?</b>
        <br>If it is true and user logged in, ID is reachable with JavaScript, example code below.
        <pre>
function getLoggedInUserID() {
    const body = document.querySelector('body');
    const classNames = body.className.split(' ');

    for (const className of classNames) {
        if (className.startsWith('logged-in-user-')) {
            const id = className.substring('logged-in-user-'.length);
            return id;
        }
    }

    return null; // Return null if the class is not found
}
        </pre>
    </li>
    <li>
        <b>SKU search is enabled?</b>
        <br>True esetében frontenden is lehetőség van cikkszámra keresni.
    </li>
    <li>
        <b>Exclude featured products from products loop?</b>
        <br>True esetében az adott kategória kiemelt termékei nem jelennek meg a ciklikusan generált tartalmak között (loop), ehelyett a kategória <code>Leírás</code> mezőjében elhelyezve az adott kategóriára vonatkozó <i>featured_products shortcode</i>-ot a kiemelt termékek az oldal tetején jelennek meg. Shortcode-ra példa: <code>[featured_products category="boritek-matrica"]</code>
    </li>
</ul>

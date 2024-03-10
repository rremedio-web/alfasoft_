<?php
/*
Plugin Name: Alfasoft Manager
Description: A plugin to manage people and their contacts.
Version: 1.0
Author: Ricardo Remédio
*/
if (!defined("ABSPATH")) {
    exit();
}

// Enqueue admin scripts and styles
function enqueue_admin_scripts()
{
    $screen = get_current_screen();
    if ($screen->base === "toplevel_page_alfasoft-manager") {
        wp_enqueue_script(
            "my-admin-js",
            plugin_dir_url(__FILE__) . "js/admin.js",
            ["jquery"],
            "1.0",
            true
        );
        wp_localize_script("my-admin-js", "myAdmin", [
            "ajaxUrl" => admin_url("admin-ajax.php"),
            "nonce" => wp_create_nonce("my_admin_nonce"),
        ]);
        wp_enqueue_style(
            "my-admin-css",
            plugin_dir_url(__FILE__) . "css/admin-styles.css"
        );
    }
}
add_action("admin_enqueue_scripts", "enqueue_admin_scripts");

function create_person_contact_tables()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $people_table = $wpdb->prefix . "people";
    $contacts_table = $wpdb->prefix . "contacts";

    $sql = "CREATE TABLE $people_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        deleted BOOLEAN DEFAULT 0,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $sql .= "CREATE TABLE $contacts_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        person_id mediumint(9) NOT NULL,
        country_code varchar(5) NOT NULL,
        number varchar(9) NOT NULL,
        PRIMARY KEY (id),
        deleted BOOLEAN DEFAULT 0,
        FOREIGN KEY (person_id) REFERENCES $people_table(id)
    ) $charset_collate;";

    require_once ABSPATH . "wp-admin/includes/upgrade.php";
    dbDelta($sql);

    // Add unique constraint to the email field in the people table
    $wpdb->query("ALTER TABLE $people_table ADD UNIQUE (email)");

    // Add unique constraint to the combination of country_code and number in the contacts table
    $wpdb->query(
        "ALTER TABLE $contacts_table ADD UNIQUE (country_code, number)"
    );
}

register_activation_hook(__FILE__, "create_person_contact_tables");

function add_person_contact_menu()
{
    add_menu_page(
        "Alfasoft Manager",
        "Alfasoft Manager",
        "manage_options",
        "alfasoft-manager",
        "render_person_contact_manager_page"
    );
}
add_action("admin_menu", "add_person_contact_menu");
function add_submenu_pages()
{
    // Submenu for managing people

    add_submenu_page(
        "alfasoft-manager", // Parent slug
        "Manage People", // Page title
        "Manage People", // Menu title
        "manage_options", // Capability
        "manage-people", // Menu slug
        "render_manage_people_page" // Function to render the page
    );

    remove_submenu_page("alfasoft-manager", "alfasoft-manager");
}
function add_edit_person_page()
{
    add_submenu_page(
        "person-manager", // Parent slug
        "Edit Person", // Page title
        "Edit Person", // Menu title
        "manage_options", // Capability
        "edit-person", // Menu slug
        "render_edit_person_page" // Function to render the page
    );
}

function add_add_contact_page()
{
    add_submenu_page(
        "person-manager", // Parent slug
        "Add Contact", // Page title
        "Add Contact", // Menu title
        "manage_options", // Capability
        "add-contact", // Menu slug
        "render_manage_contact_page" // Function to render the page
    );
}
function add_edit_contact_page()
{
    add_submenu_page(
        "person-manager", // Parent slug
        "Edit Contact", // Page title
        "Edit Contact", // Menu title
        "manage_options", // Capability
        "edit-contact", // Menu slug
        "render_edit_contact_page" // Function to render the page
    );
}

add_action("admin_menu", "add_edit_person_page");
add_action("admin_menu", "add_add_contact_page");
add_action("admin_menu", "add_edit_contact_page");
add_action("admin_menu", "add_submenu_pages");

function render_edit_person_page()
{
    global $wpdb;
    $people_table = $wpdb->prefix . "people";

    // Check if an ID is provided in the URL
    if (isset($_GET["id"])) {
        $id = intval($_GET["id"]); // Sanitize the ID to prevent SQL injection
        $person = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $people_table WHERE id = %d", $id)
        );

        // Check if form is submitted
        if (isset($_POST["edit_person"])) {
            $name = sanitize_text_field($_POST["name"]);
            $email = sanitize_email($_POST["email"]);
            $existing_person = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM $people_table WHERE email = %s",
                    $email
                )
            );

            if ($existing_person) {
                // Handle the error, e.g., display a message
                echo '<div id="message" class="error notice is-dismissible"><p>Email address already exists.</p></div>';
                return;
            }
            // Validate name length
            if (strlen($name) <= 5) {
                echo '<div id="message" class="error notice is-dismissible"><p>Name must be greater than 5 characters.</p></div>';
                echo '<a href="javascript:history.back()">Go Back</a>';
                return; // Stop execution if name is not valid
            }

            // Validate email address
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo '<div id="message" class="error notice is-dismissible"><p>Please enter a valid email address.</p></div>';
                echo '<a href="javascript:history.back()">Go Back</a>';

                return; // Stop execution if email is not valid
            }

            $wpdb->update(
                $people_table,
                [
                    "name" => $name,
                    "email" => $email,
                ],
                ["id" => $id]
            );

            echo '<div id="message" class="updated notice is-dismissible"><p>Person updated successfully.</p></div>';
            echo '<a href="' .
                esc_url(home_url("/wp-admin/admin.php?page=manage-people")) .
                '">Go Back</a>';
        }

        // Form for editing a person
        echo "<h2>Edit Person</h2>";
        echo '<form method="post">';
        echo '<input type="hidden" name="id" value="' .
            esc_attr($person->id) .
            '">';
        echo '<label for="name">Name:</label>';
        echo '<input type="text" id="name" name="name" value="' .
            esc_attr($person->name) .
            '" required><br>';
        echo '<label for="email">Email:</label>';
        echo '<input type="email" id="email" name="email" value="' .
            esc_attr($person->email) .
            '" required><br>';
        echo '<input type="submit" name="edit_person" value="Update Person">';
        echo "</form>";
    } else {
        echo "<p>No person selected for editing.</p>";
    }
}

function render_edit_contact_page()
{
    global $wpdb;
    $contacts_table = $wpdb->prefix . "contacts";

    // Fetch country data for the dropdown
    $countries = fetch_country_data();

    // Check if an ID is provided in the URL
    if (isset($_GET["id"])) {
        $id = intval($_GET["id"]); // Sanitize the ID to prevent SQL injection
        $contact = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $contacts_table WHERE id = %d", $id)
        );

        // Check if form is submitted
        if (isset($_POST["edit_contact"])) {
            $country_code = sanitize_text_field($_POST["country_code"]);
            $number = sanitize_text_field($_POST["number"]);
            $existing_contact = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM $contacts_table WHERE country_code = %s AND number = %s AND id != %d",
                    $country_code,
                    $number,
                    $id
                )
            );

            if ($existing_contact) {
                // Handle the error, e.g., display a message
                echo '<div id="message" class="error notice is-dismissible"><p>Contact already exists.</p></div>';
                return;
            }

            // Validate number length
            if (strlen($number) !== 9) {
                echo '<div id="message" class="error notice is-dismissible"><p>Number must be exactly 9 digits.</p></div>';
                return; // Stop execution if number is not valid
            }

            // Update the contact
            $wpdb->update(
                $contacts_table,
                [
                    "country_code" => $country_code,
                    "number" => $number,
                ],
                ["id" => $id]
            );

            echo '<div id="message" class="updated notice is-dismissible"><p>Contact updated successfully.</p></div>';
            echo '<a href="' .
                esc_url(home_url("/wp-admin/admin.php?page=manage-people")) .
                '">Go Back</a>';
        }

        // Form for editing a contact
        echo "<h2>Edit Contact</h2>";
        echo '<form method="post">';
        echo '<input type="hidden" name="id" value="' .
            esc_attr($contact->id) .
            '">';
        echo '<label for="country_code">Country Code:</label>';
        echo '<select id="country_code" name="country_code" required>';
        foreach ($countries as $country) {
            // Access the root calling code and any suffixes
            $root = $country["idd"]["root"];
            $suffixes = implode(", ", $country["idd"]["suffixes"]);

            // Combine the root and suffixes into a single string for display
            $callingCode = $root . " " . $suffixes . "";

            // Check if the current country code matches the existing contact's country code
            $selected =
                $callingCode == $contact->country_code ? "selected" : "";

            echo '<option value="' .
                esc_attr($callingCode) .
                '" ' .
                $selected .
                ">" .
                $country["name"]["common"] .
                " (" .
                esc_html($callingCode) .
                ")</option>";
        }
        echo "</select>";
        echo '<label for="number">Number:</label>';
        echo '<input type="text" id="number" name="number" value="' .
            esc_attr($contact->number) .
            '" required><br>';
        echo '<input type="submit" name="edit_contact" value="Update Contact">';
        echo "</form>";
    } else {
        echo "<p>No contact selected for editing.</p>";
    }
}

function render_manage_people_page()
{
    global $wpdb;
    $people_table = $wpdb->prefix . "people";

    // Handle form submissions
    if (isset($_POST["add_person"])) {
        $name = sanitize_text_field($_POST["name"]);
        $email = sanitize_email($_POST["email"]);

        // Validate name length
        if (strlen($name) <= 5) {
            echo '<div id="message" class="error notice is-dismissible"><p>Name must be greater than 5 characters.</p></div>';
            echo '<a href="javascript:history.back()">Go Back</a>';
            return; // Stop execution if name is not valid
        }

        // Validate email address
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo '<div id="message" class="error notice is-dismissible"><p>Please enter a valid email address.</p></div>';
            echo '<a href="javascript:history.back()">Go Back</a>';
            return; // Stop execution if email is not valid
        }

        $wpdb->insert($people_table, [
            "name" => $name,
            "email" => $email,
        ]);

        echo '<div id="message" class="updated notice is-dismissible"><p>Person added successfully.</p></div>';
    }

    // Handle soft delete action
    if (isset($_GET["action"]) && $_GET["action"] == "delete") {
        $id = intval($_GET["id"]);
        $wpdb->update($people_table, ["deleted" => 1], ["id" => $id]);
        echo '<div id="message" class="updated notice is-dismissible"><p>Person marked as deleted successfully.</p></div>';
    }

    // List of people with delete links
    $people = $wpdb->get_results(
        "SELECT * FROM $people_table WHERE deleted = 0"
    );
    echo '<div class="wrap">';
    echo "<h2>People</h2>";

    // Toggle button
    echo '<button id="toggle-add-person-form">Add New Person</button>';

    // Form for adding a new person
    echo '<div id="add-person-form">';
    echo '<form method="post">';
    echo '<label for="name">Name:</label>';
    echo '<input type="text" id="name" name="name" required><br>';
    echo '<label for="email">Email:</label>';
    echo '<input type="email" id="email" name="email" required><br>';
    echo '<input type="submit" name="add_person" value="Add Person">';
    echo "</form>";
    echo "</div>";

    echo '<table class="wp-list-table widefat fixed striped table-view-list">';
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr>";
    foreach ($people as $person) {
        echo "<tr>";
        echo "<td>" . esc_html($person->id) . "</td>";
        echo '<td><a href="?page=edit-person&id=' .
            $person->id .
            '">' .
            esc_html($person->name) .
            "</a></td>";
        echo "<td>" . esc_html($person->email) . "</td>";
        echo "<td>";
        echo '<a href="?page=add-contact&person_id=' .
            $person->id .
            '">Add Contact</a> | ';
        echo '<a href="?page=edit-person&id=' . $person->id . '">Edit</a> | ';
        echo '<a href="?page=manage-people&action=delete&id=' .
            $person->id .
            '">Delete</a>';
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

function fetch_country_data()
{
    $transient_name = "rest_countries_data";
    $countries = get_transient($transient_name);

    if (false === $countries) {
        $response = wp_remote_get("https://restcountries.com/v3.1/all");
        if (is_wp_error($response)) {
            return false;
        }
        $countries = json_decode(wp_remote_retrieve_body($response), true);
        set_transient($transient_name, $countries, 24 * HOUR_IN_SECONDS); // Cache for 24 hours
    }

    return $countries;
}

function render_manage_contact_page()
{
    global $wpdb;
    $contacts_table = $wpdb->prefix . "contacts";
    $people_table = $wpdb->prefix . "people"; // Assuming this is the correct table name for your people

    // Fetch person_id from the URL
    $person_id = isset($_GET["person_id"]) ? intval($_GET["person_id"]) : null;

    // Handle form submission
    if (isset($_POST["add_contact"])) {
        $countryCode = sanitize_text_field($_POST["country_code"]);
        $number = sanitize_text_field($_POST["number"]);
        $existing_contact = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $contacts_table WHERE country_code = %s AND number = %s",
                $countryCode,
                $number
            )
        );

        if ($existing_contact) {
            // Handle the error, e.g., display a message
            echo '<div id="message" class="error notice is-dismissible"><p>Contact already exists.</p></div>';
            return;
        }
        // Validate number length
        if (strlen($number) !== 9) {
            echo '<div id="message" class="error notice is-dismissible"><p>Number must be exactly 9 digits.</p></div>';
            return; // Stop execution if number is not valid
        }

        // Check if the person_id exists in the wp_people table
        if ($person_id !== null) {
            $person_exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $people_table WHERE id = %d",
                    $person_id
                )
            );

            if ($person_exists > 0) {
                // Save to database
                $wpdb->insert($contacts_table, [
                    "person_id" => $person_id,
                    "country_code" => $countryCode,
                    "number" => $number,
                ]);

                echo '<div id="message" class="updated notice is-dismissible"><p>Contact added successfully.</p></div>';
            } else {
                echo '<div id="message" class="error notice is-dismissible"><p>The selected person does not exist.</p></div>';
            }
        } else {
            echo '<div id="message" class="error notice is-dismissible"><p>No person ID provided.</p></div>';
        }
    }

    // Fetch country data for the dropdown
    $countries = fetch_country_data();

    // Render the form
    echo '<div class="wrap">';
    echo "<h2>Add New Contact</h2>";
    echo '<form method="post">';
    echo '<label for="country_code">Country:</label>';
    echo '<select id="country_code" name="country_code" required>';
    foreach ($countries as $country) {
        // Access the root calling code and any suffixes
        $root = $country["idd"]["root"];
        $suffixes = implode(", ", $country["idd"]["suffixes"]);

        // Combine the root and suffixes into a single string for display
        $callingCode = $root . " " . $suffixes . "";

        echo '<option value="' .
            esc_attr($callingCode) .
            '">' .
            $country["name"]["common"] .
            " (" .
            esc_html($callingCode) .
            ")</option>";
    }
    echo "</select>";
    echo '<label for="number">Number:</label>';
    echo '<input type="tel" id="number" name="number" pattern="\d{9}" required>';
    echo '<input type="submit" name="add_contact" value="Add Contact">';
    echo "</form>";
    echo "</div>";

    if ($person_id !== null) {
        $contacts = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $contacts_table WHERE person_id = %d",
                $person_id
            )
        );
    } else {
        // Handle the case where no person_id is provided
        echo '<div id="message" class="error notice is-dismissible"><p>No person ID provided.</p></div>';
        return;
    }
    $contacts = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $contacts_table WHERE person_id = %d AND deleted = 0",
            $person_id
        )
    );

    echo '<table class="wp-list-table widefat fixed striped">';
    echo "<thead><tr><th>ID</th><th>Country Code</th><th>Number</th><th>Actions</th></tr></thead>";
    echo "<tbody>";
    foreach ($contacts as $contact) {
        echo '<tr data-contact-id="' . esc_attr($contact->id) . '">';
        echo "<td>" . esc_html($contact->id) . "</td>";
        echo "<td>" . esc_html($contact->country_code) . "</td>";
        echo "<td>" . esc_html($contact->number) . "</td>";
        echo "<td>";
        echo '<a href="?page=edit-contact&id=' . $contact->id . '">Edit</a> | ';
        echo '<a class="delete-contact-button" href="?page=add-contact&action=soft_delete_contact&person_id=' .
            $person_id .
            "&contact_id=" .
            $contact->id .
            '" data-contact-id="' .
            $contact->id .
            '">Delete</a>';
        echo "</td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";

    if (
        isset($_GET["action"]) &&
        $_GET["action"] == "soft_delete_contact" &&
        isset($_GET["contact_id"])
    ) {
        $contact_id = intval($_GET["contact_id"]);
        $result = $wpdb->update(
            $contacts_table,
            ["deleted" => 1],
            ["id" => $contact_id]
        );

        exit();
    }
}
function alfasoft_manager_list_shortcode()
{
    global $wpdb;
    $people_table = $wpdb->prefix . "people";
    $contacts_table = $wpdb->prefix . "contacts";

    // Query to fetch all people and their contacts that are not soft deleted
    $query = "SELECT p.*, c.country_code, c.number FROM $people_table p LEFT JOIN $contacts_table c ON p.id = c.person_id WHERE p.deleted = 0 AND c.deleted = 0";
    $results = $wpdb->get_results($query);

    // Start building the output
    $output = '<div class="alfasoft-manager-list">';
    $output .= '<table id="people-contacts-table" class="table table-striped">';
    $output .= "<thead>";
    $output .= "<tr>";
    $output .= "<th>Name</th>";
    $output .= "<th>Email</th>";
    $output .= "<th>Contact</th>";
    $output .= "</tr>";
    $output .= "</thead>";
    $output .= "<tbody>";

    if ($results) {
        foreach ($results as $result) {
            $output .= "<tr>";
            $output .= "<td>" . esc_html($result->name) . "</td>";
            $output .= "<td>" . esc_html($result->email) . "</td>";
            $output .=
                "<td>" .
                esc_html($result->country_code) .
                " " .
                esc_html($result->number) .
                "</td>";
            $output .= "</tr>";
        }
    } else {
        $output .= '<tr><td colspan="3">No people found.</td></tr>';
    }

    $output .= "</tbody>";
    $output .= "</table>";
    $output .= "</div>";

    // Include JavaScript for sorting
    $output .= '<script>
    document.addEventListener("DOMContentLoaded", function() {
    var table = document.getElementById("people-contacts-table");
    var thElements = table.getElementsByTagName("th");
    var currentSortColumn = null;
    var isAscending = true;

    for (var i = 0; i < thElements.length; i++) {
        thElements[i].addEventListener("click", function() {
            var table = this.parentNode.parentNode.parentNode;
            var index = Array.prototype.indexOf.call(this.parentNode.children, this);
            var rows = Array.prototype.slice.call(table.rows, 1);

            // Determine the sorting direction
            if (currentSortColumn === index) {
                isAscending = !isAscending;
            } else {
                isAscending = true; // Default to ascending when a new column is sorted
                currentSortColumn = index;
            }

            // Sort the rows
            rows.sort(function(a, b) {
                var aText = a.children[index].textContent.toUpperCase();
                var bText = b.children[index].textContent.toUpperCase();
                return isAscending ? aText.localeCompare(bText) : bText.localeCompare(aText);
            });

            // Append the sorted rows back to the table
            for (var i = 0; i < rows.length; i++) {
                table.tBodies[0].appendChild(rows[i]);
            }

            // Update the sorting indicators
            for (var i = 0; i < thElements.length; i++) {
                thElements[i].classList.remove("sorting", "sorting-desc");
            }
            this.classList.add(isAscending ? "sorting" : "sorting-desc");
        });
    }
});

    </script>';

    return $output;
}

add_shortcode("alfasoft_manager_list", "alfasoft_manager_list_shortcode");
?>


<?php
$page_security = 'SA_PRODUCTATTRIBUTES_VARIATIONS';
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
add_access_extensions();

include_once "$path_to_root/includes/ui.inc";

page(_("Product Variations Administration"));

start_table(TABLESTYLE, "width=80%");
table_header(array(_("Feature"), _("Status"), _("Description")));

label_row(_("Core Module"), _("Required"), _("FA_ProductAttributes core module must be installed"));
label_row(_("Variations Service"), _("Available"), _("Variation generation and management"));
label_row(_("Product Types"), _("Available"), _("Simple, Variable, and Variation product types"));
label_row(_("Parent-Child Relationships"), _("Available"), _("Support for product variation hierarchies"));

end_table();

end_page();
?>
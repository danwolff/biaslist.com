# Live

The site at [biaslist.com](https://biaslist.com) is online and can be used as of the time of this edit.

# Introduction

This is a simple site design focused on presenting a searchable/filterable table presented as HTML.  The table data is contained in a SQLite db and queried from PHP.

Features include the following:

1. At `index.php` - A main, zebra-striped single-page table that can be searched/filtered from a field at the top.

2. At `editor/edit.php` - A second, behind-the-scenes editor page to add or modify entries.  Click a cell to edit it, then click Save, with some color-coding affordances during the edit flow.

The example DB, `bias_db.sqlite` has about 1000 rows in the table.

# On adaptation

The same basic pattern could be applied to other domains where occasionally searching and filtering similarly-sized tables might be a useful reference activity, and which might benefit from an equally straightforward editing/expansion flow.

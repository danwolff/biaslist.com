<?php
// index.php
try {
    $db = new PDO('sqlite:bias_db.sqlite');
    $sortColumn = $_GET['sort'] ?? 'bias_name';
    $sortDir = $_GET['dir'] ?? 'fwd';
    $filter = $_GET['q'] ?? '';
    $validColumns = ['id', 'bias_name', 'also_known_as', 'bias_description', 'reference', 'date_added', 'date_updated'];

    if (!in_array($sortColumn, $validColumns)) {
        $sortColumn = 'bias_name';
    }

    // Handle case-insensitive sorting for bias_name
    if ($sortColumn == 'bias_name') {
        $sortColumn = 'LOWER(bias_name)';
    }

    $orderDir = ($sortDir == 'rev') ? 'DESC' : 'ASC';
    $query = "SELECT *, id as last_column FROM biases ORDER BY $sortColumn $orderDir";
    $stmt = $db->query($query);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    function getSortLink($column, $currentSort, $currentDir) {
        if ($column === $currentSort) {
            $newDir = ($currentDir === 'fwd') ? 'rev' : 'fwd';
        } else {
            $newDir = 'fwd';
        }
        return "?sort=$column&dir=$newDir";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    die();
}
$totalRows = count($rows);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bias List</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* antispam */
        div.thisform_dontshow {
            display: none;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const filterInput = document.getElementById('filterInput');
            const clearButton = document.getElementById('clearButton');
            const totalRowsText = document.getElementById('totalRowsText');
            const params = new URLSearchParams(window.location.search);

            const applyFilter = () => {
                const filter = filterInput.value.toLowerCase();
                const rows = document.querySelectorAll('tbody tr');
                let matches = 0;
                let visibleIndex = 0;
                rows.forEach(row => {
                    const cells = Array.from(row.querySelectorAll('td'));
                    const matchesFilter = cells.some(td => td.textContent.toLowerCase().includes(filter));
                    if (matchesFilter) {
                        row.style.display = '';
                        row.className = (visibleIndex % 2 === 0) ? 'even' : 'odd';
                        visibleIndex++;
                        matches++;
                    } else {
                        row.style.display = 'none';
                    }
                });
                if (filter) {
                    totalRowsText.textContent = `Matches: ${matches}; Hidden by filter: ${rows.length - matches}`;
                    params.set('q', filterInput.value);
                } else {
                    totalRowsText.textContent = `Showing all ${rows.length} entries`;
                    params.delete('q');
                }
                const newUrl = params.toString() ? `${window.location.pathname}?${params.toString()}` : window.location.pathname;
                history.replaceState(null, '', newUrl);
            };

            if (params.has('q')) {
                filterInput.value = params.get('q');
                applyFilter();
            } else {
                totalRowsText.textContent = `Showing all <?= $totalRows ?> entries`;
            }

            document.querySelectorAll('.bias-name-link, .also-known-as-link').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    filterInput.value = e.target.textContent;
                    applyFilter();
                });
            });

            filterInput.addEventListener('input', applyFilter);
            clearButton.addEventListener('click', () => {
                filterInput.value = '';
                applyFilter();
            });

            // Form submission handling
            const form = document.getElementById('suggestionForm');
            const submitButton = document.getElementById('submitButton');
            const spinner = document.getElementById('spinner');

            form.addEventListener('submit', (event) => {
                submitButton.disabled = true;
                spinner.style.display = 'inline-block';
            });

        });
    </script>
</head>
<body>
    <header>
        <h1><a href=".">BiasList.com</a></h1>
        <p>An extensive list of cognitive biases and logical fallacies</p>
    </header>
    <main>
        <section class="filter-section">
            <label for="filterInput">Search/filter:</label>
            <input type="text" id="filterInput" placeholder="Filter...">
            <button id="clearButton">Clear</button>
            <p id="totalRowsText">Showing all <?= $totalRows ?> entries</p>
        </section>
        <section class="table-section">
            <table>
                <thead>
                    <tr>
                        <th><a href="<?= getSortLink('bias_name', $_GET['sort'] ?? 'bias_name', $_GET['dir'] ?? 'fwd') ?>">Bias or fallacy name</a></th>
                        <th><a href="<?= getSortLink('also_known_as', $_GET['sort'] ?? 'bias_name', $_GET['dir'] ?? 'fwd') ?>">Also known as</a></th>
                        <th><a href="<?= getSortLink('bias_description', $_GET['sort'] ?? 'bias_name', $_GET['dir'] ?? 'fwd') ?>">Description</a></th>
                        <th><a href="<?= getSortLink('reference', $_GET['sort'] ?? 'bias_name', $_GET['dir'] ?? 'fwd') ?>">Reference</a></th>
                        <th><a href="<?= getSortLink('date_added', $_GET['sort'] ?? 'bias_name', $_GET['dir'] ?? 'fwd') ?>">Added</a></th>
                        <th><a href="<?= getSortLink('date_updated', $_GET['sort'] ?? 'bias_name', $_GET['dir'] ?? 'fwd') ?>">Updated</a></th>
                        <th><a href="<?= getSortLink('id', $_GET['sort'] ?? 'bias_name', $_GET['dir'] ?? 'fwd') ?>">ID</a></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $index => $row): ?>
                        <tr class="<?= $index % 2 == 0 ? 'even' : 'odd' ?>">
                            <td data-column="bias_name">
                                <a href="#" class="bias-name-link"><?= nl2br(htmlspecialchars($row['bias_name'], ENT_QUOTES)) ?></a>
                            </td>
                            <td data-column="also_known_as">
                                <?php
                                $links = array_map(function($item) {
                                    return '<a href="#" class="also-known-as-link">' . nl2br(htmlspecialchars(trim($item), ENT_QUOTES)) . '</a>';
                                }, explode(',', $row['also_known_as']));
                                echo implode(', ', $links);
                                ?>
                            </td>
                            <td data-column="bias_description"><?= nl2br(htmlspecialchars($row['bias_description'], ENT_QUOTES)) ?></td>
                            <td data-column="reference"><?= nl2br(htmlspecialchars($row['reference'], ENT_QUOTES)) ?></td>
                            <td data-column="date_added"><?= nl2br(htmlspecialchars($row['date_added'], ENT_QUOTES)) ?></td>
                            <td data-column="date_updated"><?= nl2br(htmlspecialchars($row['date_updated'], ENT_QUOTES)) ?></td>
                            <td data-column="id"><?= nl2br(htmlspecialchars($row['id'], ENT_QUOTES)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <section class="export-section">
            <a href="export_csv.php" class="button">Export CSV</a>
        </section>
        <section class="citations-section">
            <h3>References</h3>
            <ul>
                <li>[1] Dr. L. Kip Wheeler, <a href="https://web.archive.org/web/20230816004621/https://web.cn.edu/kwheeler/fallacies_list.html">Logical Fallacies Handlist</a>, (2023 archive, 2024 retrieved).</li>
                <li>[2] UVU Writing Center, <a href="https://www.uvu.edu/writingcenter/docs/logicalfallacies.pdf">Logical Fallacies</a>, Utah Valley University, (2024 retrieved).</li>
                <li>[3] Oren M. Williamson, <a href="https://utminers.utep.edu/omwilliamson/engl1311/fallacies.htm">Master List of Logical Fallacies</a>, The University of Texas at El Paso, (2024 retrieved).</li>
                <li>[4] Mathew, <a href="https://web.archive.org/web/20020228195548/https://www.cse.unsw.edu.au/~timm/pub/guides/Logic.html">Constructing a Logical Argument</a>, School of Computer Science and Engineering, University of New South Wales, (2002 archive, 2024 retrieved).</li>
                <li>[5] <a href="https://en.wikipedia.org/wiki/Motte-and-bailey_fallacy">Motte-and-bailey fallacy</a>, Wikipedia.</li>
                <li>[6] <a href="https://en.wikipedia.org/wiki/List_of_cognitive_biases">List of cognitive biases</a>, Wikipedia.</li>
                <li>[7] <a href="https://iep.utm.edu/fallacy/">Internet Encyclopedia of Philosophy</a>, Internet Encyclopedia of Philosophy, Chase B. Wrenn, ISSN 2161-0002.</li>
                <li>[8] <a href="https://www.logical-fallacy.com/list-of-logical-fallacies/">logical-fallacy.com</a>, List of Logical Fallacies.</li>
                <li>[9] <a href="https://www.logical-fallacy.com/articles/list-of-informal-fallacies/">logical-fallacy.com</a>, List of Informal Logical Fallacies.</li>
                <li>[10] <a href="https://www.logical-fallacy.com/articles/list-of-formal-fallacies/">logical-fallacy.com</a>, List of Formal Logical Fallacies.</li>
                <li>[11] <a href="https://www.logical-fallacy.com/articles/name-calling/">logical-fallacy.com</a>, Name Calling - Definition and Examples.</li>
                <li>[12] <a href="https://www.dbu.edu/naugle/courses/_documents/phil-2302/handouts/informal-fallacies/introduction.pdf">Introduction to Informal Fallacies</a>, Dr. Naugle, Phil 3304 Logic, (dbu.edu).</li>
                <li>[13] <a href="https://www.dbu.edu/naugle/courses/_documents/phil-2302/handouts/informal-fallacies/relevance-part-i.pdf">Fallacies of Relevance</a>, Dr. Naugle, Phil 2303 Logic, (dbu.edu).</li> 
                <li>[14] <a href="https://www.dbu.edu/naugle/courses/_documents/phil-2302/handouts/informal-fallacies/relevance-part-ii.pdf">Fallacies of Relevance Continued</a>, Dr. Naugle, Phil 2303 Logic, (dbu.edu).</li> 
                <li>[15] <a href="https://philosophy.lander.edu/logic/person.html">Ad Hominem and Related Fallacies</a>, Introduction to Logic, (philosophy.lander.edu).</li> 
                <li>[16] <a href="https://en.wikipedia.org/wiki/Chronological_snobbery">Chronological snobbery</a>, Wikipedia.</li>
                <li>[17] <a href="https://en.wikipedia.org/wiki/Cognitive_dissonance">Cognitive dissonance</a>, Wikipedia.</li>
                <li>[18] <a href="https://en.wikipedia.org/wiki/Misinformation_effect">Misinformation effect</a>, Wikipedia.</li>
                <li>[19] <a href="https://en.wikipedia.org/wiki/Anthropic_principle">Anthropic principle</a>, Wikipedia.</li>
                <li>[20] <a href="https://en.wikipedia.org/wiki/Selection_bias">Selection bias</a>, Wikipedia.</li>
                <li>[21] <a href="https://en.wikipedia.org/wiki/Dynamic_inconsistency">Dynamic inconsistency</a>, Wikipedia.</li>
                <li>[22] <a href="https://en.wikipedia.org/wiki/Texas_sharpshooter_fallacy">Texas sharpshooter fallacy</a>, Wikipedia.</li>
                <li>[23] <a href="https://en.wikipedia.org/wiki/In-group_favoritism">In-group favoritism</a>, Wikipedia.</li>
                <li>[24] <a href="https://en.wikipedia.org/wiki/Herd_behavior">Herd behavior</a>, Wikipedia.</li> 
                <li>[25] <a href="https://plato.stanford.edu/">Stanford Encyclopedia of Philosophy (SEP)</a>, (plato.stanford.edu).</li>
                <li>[26] <a href="https://iep.utm.edu/">Internet Encyclopedia of Philosophy (IEP)</a>, (iep.utm.edu).</li>
                <li>[27] Alex McCraw, <a href="https://blog.alexmaccaw.com/common-logical-fallacies-surrounding-capitalism/">Common Logical Fallacies surrounding capitallism</a>, (2024, blog.alexmaccaw.com).</li>
                <li>[28] <a href="https://en.wikipedia.org/wiki/Positive_outcome_bias">Positive outcome bias</a>, Wikipedia.</li>
                <li>[29] <a href="https://en.wikipedia.org/wiki/Optimism_bias">Optimisim bias</a>, Wikipedia.</li>
                <li>[30] <a href="https://en.wikipedia.org/wiki/Wishful_thinking">Wishful thinking</a>, Wikipedia.</li>
                <li>[31] <a href="https://en.wikipedia.org/wiki/Converse_accident">Converse accident</a>, Wikipedia.</li>
                <li>[32] <a href="https://en.wikipedia.org/wiki/Secundum_quid">Secundum quid</a>, Wikipedia.</li>
                <!-- <li>[] <a href=""></a></li> -->
            </ul>
            <h3>Clarification</h3>
            <p>Conceptually, cognitive biases are considered distinct from logical fallacies. The <b>cb</b> and <b>lf</b> suffixes in the <b>id</b> column roughly distinguish these categories. Logical fallacies represent argumentation errors, whereas cognitive biases describe mental tendencies that could result in processing errors. Both phenomenon types could result in errors in thought or decision. The list is combined for convenient lookups. 
        </section>
        <section class="suggestion-form-section">
            <h3>Suggest an addition or improvement</h3>
            <form id="suggestionForm" action="https://submit-form.com/LnhuYO9ra" method="POST">
                <label for="biasOrFallacyName">Bias or fallacy name <span style="font-size:0.8em;color:red">required</span>:</label>
                <input type="text" id="biasOrFallacyName" name="bias_or_fallacy_name" required>
                
                <label for="alsoKnownAs">Also known as:</label>
                <input type="text" id="alsoKnownAs" name="also_known_as">
                
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4"></textarea>
                
                <label for="reference">Reference(s):</label>
                <textarea id="reference" name="reference" rows="3"></textarea>

                <div class="thisform_dontshow"><input name="email" type="text"></div>
                
                <button id="submitButton" type="submit">Send</button>
                <div id="spinner" style="display: none;">
                    <svg class="spinner" width="24" height="24" viewBox="0 0 24 24">
                        <circle class="path" cx="12" cy="12" r="10" fill="none" stroke-width="2"></circle>
                    </svg>
                </div>
            </form>
        </section>
    </main>
    <footer>
        <div class="footer-content">
            <p><strong>Suggestions:</strong> Have a cognitive bias or logical fallacy to add? Or another idea? Please email it (anonymously if you wish) to <a href="mailto:contact@BiasList.com">contact@BiasList.com</a>.</p>
            <p><strong>Privacy notice:</strong> BiasList.com does not use cookies or collect personal information. Your privacy is respected here.</p>
        </div>
    </footer>
</body>
</html>

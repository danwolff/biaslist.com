document.addEventListener('DOMContentLoaded', () => {
    const saveButtons = document.querySelectorAll('.save-btn');
    const deleteButtons = document.querySelectorAll('.delete-btn');
    const addRowButton = document.getElementById('add-row-btn');

    function saveRow(button) {
        const row = button.closest('tr');
        const originalId = row.dataset.id;
        const cells = row.querySelectorAll('td[contenteditable="true"]');
        const data = {};

        cells.forEach(cell => {
            const column = cell.dataset.column;
            // Convert <br> to newline and remove other HTML tags
            data[column] = cell.innerText.replace(/\n/g, '<br>').replace(/<\/?[^>]+(>|$)/g, '');
        });

        fetch('update_bias.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: originalId || null, data })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                button.disabled = true;
                button.style.backgroundColor = '';
                cells.forEach(cell => cell.style.backgroundColor = '');
                row.dataset.id = result.id;
            } else {
                alert('Failed to save changes: ' + result.message);
            }
        });
    }

    function enableSaveButton(cell) {
        cell.style.backgroundColor = 'red';
        const row = cell.closest('tr');
        const saveButton = row.querySelector('.save-btn');
        saveButton.disabled = false;
        saveButton.style.backgroundColor = 'aqua';
    }

    saveButtons.forEach(button => {
        button.addEventListener('click', () => saveRow(button));
    });

    deleteButtons.forEach(button => {
        button.addEventListener('click', () => {
            const row = button.closest('tr');
            const id = row.dataset.id;
            const data = {};
            const biasName = row.querySelector('td[data-column="bias_name"]').textContent;

            row.querySelectorAll('td[contenteditable="true"]').forEach(cell => {
                const column = cell.dataset.column;
                // Convert <br> to newline and remove other HTML tags
                data[column] = cell.innerText.replace(/\n/g, '<br>').replace(/<\/?[^>]+(>|$)/g, '');
            });

            if (confirm(`Are you sure you want to delete "${biasName}"?`)) {
                fetch('delete_bias.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id, data })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        row.remove();
                    } else {
                        alert('Failed to delete the entry: ' + result.message);
                    }
                });
            }
        });
    });

    document.querySelectorAll('td[contenteditable="true"]').forEach(cell => {
        cell.addEventListener('input', () => enableSaveButton(cell));
    });

    addRowButton.addEventListener('click', () => {
        const newRow = document.querySelector('.new-row').cloneNode(true);
        newRow.classList.remove('new-row');
        newRow.dataset.id = '';
        newRow.querySelectorAll('td[contenteditable="true"]').forEach(cell => {
            cell.innerHTML = '';  // Clear the cell content
            cell.addEventListener('input', () => enableSaveButton(cell));  // Enable save button on input
        });
        const saveButton = newRow.querySelector('.save-btn');
        saveButton.addEventListener('click', () => saveRow(saveButton));
        document.querySelector('tbody').appendChild(newRow);
    });
});

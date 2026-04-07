document.addEventListener('DOMContentLoaded', function() {
    // 1. Automatic Due Date calculation
    const loanDateInput = document.getElementById('loan_date');
    const dueDateInput = document.getElementById('due_date');

    if (loanDateInput && dueDateInput) {
        loanDateInput.addEventListener('change', function() {
            if (this.value) {
                const date = new Date(this.value);
                date.setDate(date.getDate() + 7);
                const yyyy = date.getFullYear();
                const mm = String(date.getMonth() + 1).padStart(2, '0');
                const dd = String(date.getDate()).padStart(2, '0');
                dueDateInput.value = `${yyyy}-${mm}-${dd}`;
            }
        });
    }

    // 2. TablePress Search (Advanced Mode)
    const btnSearch = document.getElementById('btn_search_game');
    const titleInput = document.getElementById('game_title');
    const refInput = document.getElementById('game_ref');
    const sourceSelect = document.getElementById('game_source');

    if (btnSearch && titleInput && refInput && sourceSelect) {
        btnSearch.addEventListener('click', function() {
            const source = sourceSelect.value;
            const query = refInput.value.trim();
            
            if (!query) {
                alert(bgLoansAdmin.i18n.enterQuery);
                return;
            }

            if (source !== 'tablepress') {
                alert(bgLoansAdmin.i18n.onlyTablePress);
                return;
            }

            btnSearch.disabled = true;
            btnSearch.textContent = bgLoansAdmin.i18n.searching;

            let oldList = document.getElementById('bg-loans-tp-results');
            if (oldList) oldList.remove();

            const data = new URLSearchParams({
                action: 'bg_loans_search_tablepress',
                q: query
            });

            fetch(bgLoansAdmin.ajaxurl, {
                method: 'POST',
                body: data
            })
            .then(response => response.json())
            .then(res => {
                btnSearch.disabled = false;
                btnSearch.textContent = bgLoansAdmin.i18n.search;

                if (!res.success) {
                    alert(bgLoansAdmin.i18n.tpError + ' ' + (res.data || bgLoansAdmin.i18n.unknownError));
                    return;
                }

                if (!res.data || res.data.length === 0) {
                    alert(bgLoansAdmin.i18n.noResults);
                    return;
                }

                const results = res.data;

                if (results.length === 1) {
                    refInput.value = results[0].id;
                    titleInput.value = results[0].title;
                } else {
                    const container = document.createElement('div');
                    container.id = 'bg-loans-tp-results';
                    container.style.marginTop = '10px';
                    
                    const select = document.createElement('select');
                    select.style.maxWidth = '300px';
                    
                    const defaultOpt = document.createElement('option');
                    defaultOpt.value = '';
                    defaultOpt.textContent = bgLoansAdmin.i18n.selectGame;
                    select.appendChild(defaultOpt);

                    results.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item.id;
                        opt.dataset.title = item.title;
                        const yearStr = item.year ? ` (${item.year})` : '';
                        opt.textContent = item.title + yearStr;
                        select.appendChild(opt);
                    });

                    const applyBtn = document.createElement('button');
                    applyBtn.type = 'button';
                    applyBtn.className = 'button';
                    applyBtn.style.marginLeft = '5px';
                    applyBtn.textContent = bgLoansAdmin.i18n.apply;
                    
                    applyBtn.addEventListener('click', function() {
                        if (select.value) {
                            refInput.value = select.value;
                            titleInput.value = select.options[select.selectedIndex].dataset.title;
                            container.remove();
                        }
                    });

                    container.appendChild(select);
                    container.appendChild(applyBtn);
                    refInput.parentNode.appendChild(container);
                }
            })
            .catch(err => {
                btnSearch.disabled = false;
                btnSearch.textContent = bgLoansAdmin.i18n.search;
                alert(bgLoansAdmin.i18n.serverError);
            });
        });
    }
});

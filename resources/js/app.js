/**
 * My Self-Care – Core Application JavaScript
 * All interactive functionality, modal controllers, and Chart.js initializers.
 */

window.SelfCare = {
    /**
     * Modal management
     */
    openModal(modalId) {
        const el = document.getElementById(modalId);
        if (el) {
            el.classList.add('active');
            el.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            if (window.lucide) {
                window.lucide.createIcons();
            }
        }
    },

    closeModal(modalId) {
        const el = document.getElementById(modalId);
        if (el) {
            el.classList.remove('active');
            el.style.display = 'none';
            document.body.style.overflow = '';
        }
    },

    /**
     * Mobile sidebar navigation
     */
    openSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (sidebar) sidebar.classList.add('open');
        if (overlay) overlay.classList.add('open');
    },

    closeSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (sidebar) sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('open');
    },

    /**
     * Quick pre-fill helpers
     */
    prefillHabit(name, category, modalId = 'habitModal') {
        const nameInput = document.getElementById('habit_name');
        const catInput = document.getElementById('habit_category');
        if (nameInput) nameInput.value = name;
        if (catInput) catInput.value = category;
        this.openModal(modalId);
    },

    prefillWorkout(type, modalId = 'exerciseModal') {
        const typeInput = document.getElementById('workout_type');
        if (typeInput) typeInput.value = type;
        this.openModal(modalId);
    },

    /**
     * Food nutrient calculator from serving size
     */
    updateFoodNutritionFromServing() {
        const select = document.getElementById('food_item_id');
        const qtyInput = document.getElementById('quantity');
        if (!select || !qtyInput) return;

        const opt = select.selectedOptions[0];
        if (!opt || !opt.dataset.calories) return;

        const qty = parseFloat(qtyInput.value) || 1;
        const cal = parseFloat(opt.dataset.calories) || 0;
        const pro = parseFloat(opt.dataset.protein) || 0;
        const car = parseFloat(opt.dataset.carbs) || 0;
        const fat = parseFloat(opt.dataset.fat) || 0;

        const calInput = document.getElementById('calories');
        const proInput = document.getElementById('protein');
        const carInput = document.getElementById('carbohydrates');
        const fatInput = document.getElementById('fat');
        const nameInput = document.getElementById('food_name');

        if (calInput) calInput.value = Math.round(cal * qty);
        if (proInput) proInput.value = (pro * qty).toFixed(1);
        if (carInput) carInput.value = (car * qty).toFixed(1);
        if (fatInput) fatInput.value = (fat * qty).toFixed(1);
        if (nameInput && opt.text && !nameInput.value) {
            nameInput.value = opt.text.split('(')[0].trim();
        }
    },

    /**
     * Image preview helper
     */
    previewImage(input, previewElementId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById(previewElementId);
                if (preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    },

    /**
     * Chart Initializers
     */
    initWeeklyCaloriesChart(canvasId, days, calories) {
        const ctx = document.getElementById(canvasId);
        if (!ctx || typeof Chart === 'undefined') return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: days,
                datasets: [{
                    label: 'Calories',
                    data: calories,
                    backgroundColor: '#EDE9FE',
                    hoverBackgroundColor: '#8E8CD8',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    },

    initExerciseTrendChart(canvasId, labels, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx || typeof Chart === 'undefined') return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Active Minutes',
                    data: data,
                    backgroundColor: '#7FA68E',
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    },

    initSleepTrendChart(canvasId, labels, hours) {
        const ctx = document.getElementById(canvasId);
        if (!ctx || typeof Chart === 'undefined') return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Hours Slept',
                    data: hours,
                    backgroundColor: '#8E8CD8',
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 12,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: { callback: v => v + 'h' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    },

    initEnergyBalanceChart(canvasId, labels, caloriesIn, workoutMins) {
        const ctx = document.getElementById(canvasId);
        if (!ctx || typeof Chart === 'undefined') return;

        new Chart(energyCtx = ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Calories In (kcal)',
                        data: caloriesIn,
                        backgroundColor: '#F4A261',
                        borderRadius: 6,
                    },
                    {
                        label: 'Exercise (mins)',
                        data: workoutMins,
                        backgroundColor: '#7FA68E',
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    },

    initSleepWaterChart(canvasId, labels, sleepHours, waterMl) {
        const ctx = document.getElementById(canvasId);
        if (!ctx || typeof Chart === 'undefined') return;

        new Chart(ctx, {
            data: {
                labels: labels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Sleep Duration (hrs)',
                        data: sleepHours,
                        backgroundColor: '#8E8CD8',
                        borderRadius: 6,
                        yAxisID: 'ySleep'
                    },
                    {
                        type: 'line',
                        label: 'Water Intake (ml)',
                        data: waterMl,
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.3,
                        yAxisID: 'yWater'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    ySleep: {
                        type: 'linear',
                        position: 'left',
                        beginAtZero: true,
                        max: 12,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: { callback: v => v + 'h' }
                    },
                    yWater: {
                        type: 'linear',
                        position: 'right',
                        beginAtZero: true,
                        grid: { display: false },
                        ticks: { callback: v => v + 'ml' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    },

    initFoodCalorieTrendChart(canvasId, labels, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx || typeof Chart === 'undefined') return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Calories',
                    data: data,
                    backgroundColor: 'rgba(142, 140, 216, 0.45)',
                    borderColor: 'rgba(142, 140, 216, 1)',
                    borderWidth: 1.5,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => `${ctx.parsed.y.toFixed(0)} kcal` } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: { font: { size: 11 }, callback: v => v + ' kcal' }
                    },
                    x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 45 } }
                }
            }
        });
    },

    initFoodMacroDonutChart(canvasId, macroData) {
        const ctx = document.getElementById(canvasId);
        if (!ctx || typeof Chart === 'undefined') return;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Protein', 'Carbohydrates', 'Fat'],
                datasets: [{
                    data: [macroData.protein || 1, macroData.carbs || 1, macroData.fat || 1],
                    backgroundColor: ['#7FA68E', '#F4A261', '#EC4899'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}%` } }
                },
                cutout: '65%',
            }
        });
    },

    initFinanceIncomeExpenseChart(canvasId, labels, incomeData, expenseData) {
        const ctx = document.getElementById(canvasId);
        if (!ctx || typeof Chart === 'undefined') return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Income',
                        data: incomeData,
                        backgroundColor: 'rgba(127, 166, 142, 0.6)',
                        borderColor: '#7FA68E',
                        borderWidth: 1.5,
                        borderRadius: 6,
                    },
                    {
                        label: 'Expense',
                        data: expenseData,
                        backgroundColor: 'rgba(244, 162, 97, 0.6)',
                        borderColor: '#F4A261',
                        borderWidth: 1.5,
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.dataset.label}: ₹${ctx.parsed.y.toLocaleString('en-IN')}`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: { callback: v => '₹' + v.toLocaleString('en-IN') }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    },

    initFinanceCategoryChart(canvasId, labels, data, colors) {
        const ctx = document.getElementById(canvasId);
        if (!ctx || typeof Chart === 'undefined') return;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    hoverOffset: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ₹${ctx.parsed.toLocaleString('en-IN')}`
                        }
                    }
                },
                cutout: '62%',
            }
        });
    },

    initFinanceDailyExpenseChart(canvasId, labels, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx || typeof Chart === 'undefined') return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Expense (₹)',
                    data: data,
                    backgroundColor: 'rgba(244, 162, 97, 0.5)',
                    borderColor: '#F4A261',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ` ₹${ctx.parsed.y.toLocaleString('en-IN')}` } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: { callback: v => '₹' + v }
                    },
                    x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 45 } }
                }
            }
        });
    }
};

// Global shorthand functions for clean Blade template calls
window.openModal = (id) => window.SelfCare.openModal(id);
window.closeModal = (id) => window.SelfCare.closeModal(id);
window.openSidebar = () => window.SelfCare.openSidebar();
window.closeSidebar = () => window.SelfCare.closeSidebar();
window.prefillHabit = (name, cat) => window.SelfCare.prefillHabit(name, cat);
window.prefillWorkout = (type) => window.SelfCare.prefillWorkout(type);

// Initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) {
        window.lucide.createIcons();
    }

    // Auto-dismiss alert messages after 5 seconds
    const flashMessages = document.getElementById('flashMessages');
    if (flashMessages) {
        setTimeout(() => {
            flashMessages.style.transition = 'opacity 0.5s ease';
            flashMessages.style.opacity = '0';
            setTimeout(() => flashMessages.remove(), 500);
        }, 5000);
    }

    // Close modals on clicking overlay backdrop
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                window.SelfCare.closeModal(overlay.id);
            }
        });
    });

    // Close modals on Esc key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(overlay => {
                window.SelfCare.closeModal(overlay.id);
            });
        }
    });

    // Auto-init charts if elements with data attributes exist
    document.querySelectorAll('[data-chart]').forEach(canvas => {
        const type = canvas.dataset.chart;
        try {
            const chartData = JSON.parse(canvas.dataset.chartData || '{}');
            if (type === 'weekly-calories') {
                window.SelfCare.initWeeklyCaloriesChart(canvas.id, chartData.labels, chartData.data);
            } else if (type === 'exercise-trend') {
                window.SelfCare.initExerciseTrendChart(canvas.id, chartData.labels, chartData.data);
            } else if (type === 'sleep-trend') {
                window.SelfCare.initSleepTrendChart(canvas.id, chartData.labels, chartData.data);
            } else if (type === 'energy-balance') {
                window.SelfCare.initEnergyBalanceChart(canvas.id, chartData.labels, chartData.calories, chartData.exercise);
            } else if (type === 'sleep-water') {
                window.SelfCare.initSleepWaterChart(canvas.id, chartData.labels, chartData.sleep, chartData.water);
            } else if (type === 'water-history') {
                window.SelfCare.initWaterHistoryChart(canvas.id, chartData.history, chartData.goal);
            } else if (type === 'food-calorie-trend') {
                window.SelfCare.initFoodCalorieTrendChart(canvas.id, chartData.labels, chartData.data);
            } else if (type === 'food-macro-donut') {
                window.SelfCare.initFoodMacroDonutChart(canvas.id, chartData);
            } else if (type === 'finance-income-expense') {
                window.SelfCare.initFinanceIncomeExpenseChart(canvas.id, chartData.labels, chartData.income, chartData.expense);
            } else if (type === 'finance-category-spending') {
                window.SelfCare.initFinanceCategoryChart(canvas.id, chartData.labels, chartData.data, chartData.colors);
            } else if (type === 'finance-daily-expense') {
                window.SelfCare.initFinanceDailyExpenseChart(canvas.id, chartData.labels, chartData.data);
            }
        } catch (e) {
            console.error('Failed to init chart:', e);
        }
    });

    // Ensure checked meal type radio has .selected on its card on load
    const checkedMealRadio = document.querySelector('input[name="meal_type"]:checked');
    if (checkedMealRadio) {
        window.updateMealSelection(checkedMealRadio);
    }

    // Event delegation for meal selection change
    document.addEventListener('change', (e) => {
        if (e.target && e.target.matches('input[name="meal_type"]')) {
            window.updateMealSelection(e.target);
        }
    });
});

/**
 * Transaction type toggle
 */
window.switchTransactionType = function(type) {
    const badgeExp = document.getElementById('badge_expense');
    const badgeInc = document.getElementById('badge_income');
    const groupExp = document.getElementById('expenseCategoryGroup');
    const groupInc = document.getElementById('incomeCategoryGroup');

    if (type === 'expense') {
        if (badgeExp) badgeExp.classList.add('active-expense');
        if (badgeInc) badgeInc.classList.remove('active-income');
        if (groupExp) groupExp.style.display = 'block';
        if (groupInc) groupInc.style.display = 'none';
    } else {
        if (badgeInc) badgeInc.classList.add('active-income');
        if (badgeExp) badgeExp.classList.remove('active-expense');
        if (groupExp) groupExp.style.display = 'none';
        if (groupInc) groupInc.style.display = 'block';
    }
};

window.updateMealSelection = function(radio) {
    if (!radio) return;
    document.querySelectorAll('.meal-type-badge, .meal-card-selector').forEach(b => b.classList.remove('selected'));
    const parent = radio.closest('label');
    if (parent) {
        const badge = parent.querySelector('.meal-type-badge, .meal-card-selector');
        if (badge) badge.classList.add('selected');
    }
};

/**
 * Budget & Savings Modal Controllers
 */
window.openBudgetModal = function(catId, catName, currentLimit, threshold) {
    const catInput = document.getElementById('modal_category_id');
    const amtInput = document.getElementById('modal_amount_limit');
    const thrInput = document.getElementById('modal_alert_threshold');
    const titleEl = document.getElementById('budgetModalTitle');

    if (catInput) catInput.value = catId || '';
    if (amtInput) amtInput.value = currentLimit || '';
    if (thrInput) thrInput.value = threshold || 80;
    if (titleEl) titleEl.textContent = catName ? `Set Limit for ${catName}` : 'Set Overall Monthly Budget Limit';

    window.SelfCare.openModal('budgetModal');
};

window.openAddFundsModal = function(goalId, goalName) {
    const title = document.getElementById('addFundsTitle');
    const form = document.getElementById('addFundsForm');
    const dep = document.getElementById('deposit_amount');

    if (title) title.textContent = `Add Funds to ${goalName}`;
    if (form) form.action = `/finance/savings/${goalId}/add-funds`;
    if (dep) dep.value = '';

    window.SelfCare.openModal('addFundsModal');
};

window.openEditGoalModal = function(goalId, name, target, current, targetDate, notes, status) {
    const form = document.getElementById('editGoalForm');
    if (form) form.action = `/finance/savings/${goalId}`;

    const n = document.getElementById('edit_goal_name');
    const t = document.getElementById('edit_target_amount');
    const c = document.getElementById('edit_current_saved_amount');
    const d = document.getElementById('edit_target_date');
    const nt = document.getElementById('edit_notes');
    const s = document.getElementById('edit_status');

    if (n) n.value = name;
    if (t) t.value = target;
    if (c) c.value = current;
    if (d) d.value = targetDate;
    if (nt) nt.value = notes;
    if (s) s.value = status;

    window.SelfCare.openModal('editGoalModal');
};

/**
 * Profile avatar preview
 */
window.previewAvatar = function(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const preview = document.getElementById('avatarPreview');
            if (preview) { preview.src = e.target.result; }
        };
        reader.readAsDataURL(input.files[0]);
    }
};

window.SelfCare.initWaterHistoryChart = function(canvasId, historyData, goal) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || typeof Chart === 'undefined') return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: historyData.map(d => d.label),
            datasets: [
                {
                    label: 'Water Logged (ml)',
                    data: historyData.map(d => d.amount),
                    backgroundColor: 'rgba(59, 130, 246, 0.45)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1.5,
                    borderRadius: 6,
                },
                {
                    type: 'line',
                    label: 'Daily Target (' + goal + ' ml)',
                    data: historyData.map(() => goal),
                    borderColor: '#10B981',
                    borderDash: [5, 5],
                    borderWidth: 2,
                    pointRadius: 0,
                    fill: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: v => v + ' ml', font: { size: 10 } },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: { grid: { display: false }, ticks: { font: { size: 10 } } }
            }
        }
    });
};

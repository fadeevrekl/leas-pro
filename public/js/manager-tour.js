// public/js/shepherd-tour.js - РАБОЧИЙ ВАРИАНТ С ОБВОДКОЙ И ОВЕРЛЕЕМ

class ManagerTour {
    constructor() {
        this.tour = null;
        this.currentPage = this.getCurrentPage();
        this.isManager = window.userRole === 'manager';
        this.isInitialized = false;
    }
    
    getCurrentPage() {
        const path = window.location.pathname;
        
        // Определяем страницу сделок
        if (path.includes('/deals/create')) return 'dealsCreate';
        if (path.includes('/deals/edit')) return 'dealsEdit';
        if (path.match(/\/deals\/\d+$/)) return 'dealsShow';
        if (path.includes('/deals')) return 'dealsIndex';
        
        // Определяем страницу клиентов
        if (path.includes('/clients/create')) return 'clientsCreate';
        if (path.includes('/clients/edit')) return 'clientsEdit';
        if (path.match(/\/clients\/\d+$/)) return 'clientsShow';
        if (path.includes('/clients')) return 'clientsIndex';
        
        // Определяем страницу автомобилей
        if (path.includes('/cars/create')) return 'carsCreate';
        if (path.includes('/cars/edit')) return 'carsEdit';
        if (path.match(/\/cars\/\d+$/)) return 'carsShow';
        if (path.includes('/cars')) return 'carsIndex';
        
        return null;
    }
    
    shouldShowTour() {
        if (!this.isManager || !this.currentPage) {
            console.log('Тур не показываем. Роль:', window.userRole, 'Страница:', this.currentPage);
            return false;
        }
        
        const pageTourKey = `tourCompleted_${this.currentPage}`;
        return !localStorage.getItem(pageTourKey);
    }
    
    markTourCompleted() {
        const pageTourKey = `tourCompleted_${this.currentPage}`;
        localStorage.setItem(pageTourKey, 'true');
    }
    
    createTour() {
        const steps = this.getStepsForPage();
        if (steps.length === 0) {
            console.log('Нет шагов для страницы:', this.currentPage);
            return;
        }
        
        console.log('Создаем тур с', steps.length, 'шагами');
        
        // Проверяем, что Shepherd загружен
        if (typeof Shepherd === 'undefined') {
            console.error('Shepherd не загружен');
            return;
        }
        
        try {
            this.tour = new Shepherd.Tour({
                // ВКЛЮЧАЕМ МОДАЛЬНЫЙ ОВЕРЛЕЙ!
                useModalOverlay: true,
                defaultStepOptions: {
                    cancelIcon: {
                        enabled: true,
                        label: '✕'
                    },
                    scrollTo: { 
                        behavior: 'smooth', 
                        block: 'center'
                    },
                    classes: 'shepherd-theme-arrows',
                    arrow: true,
                    // Добавляем кастомные классы для подсветки
                    highlightClass: 'tour-highlight-element'
                }
            });
            
            steps.forEach((step, index) => {
                const buttons = [];
                const totalSteps = steps.length;
                
                // Кнопка пропуска всегда первая
                buttons.push({
                    text: '✕ Пропустить тур',
                    action: this.tour.cancel,
                    classes: 'shepherd-button-skip'
                });
                
                // Для всех шагов, кроме последнего, добавляем "Далее"
                if (index < totalSteps - 1) {
                    buttons.push({
                        text: 'Далее →',
                        action: this.tour.next,
                        classes: 'shepherd-button-primary'
                    });
                }
                
                // Для всех шагов, кроме первого, добавляем "Назад"
                if (index > 0) {
                    const nextIndex = buttons.findIndex(b => b.text === 'Далее →');
                    if (nextIndex > -1) {
                        buttons.splice(nextIndex, 0, {
                            text: '← Назад',
                            action: this.tour.back,
                            classes: 'shepherd-button-secondary'
                        });
                    } else {
                        buttons.push({
                            text: '← Назад',
                            action: this.tour.back,
                            classes: 'shepherd-button-secondary'
                        });
                    }
                }
                
                // Для последнего шага заменяем "Далее" на "Завершить"
                if (index === totalSteps - 1) {
                    const nextIndex = buttons.findIndex(b => b.text === 'Далее →');
                    if (nextIndex > -1) {
                        buttons.splice(nextIndex, 1);
                    }
                    buttons.push({
                        text: '✅ Завершить',
                        action: this.tour.complete,
                        classes: 'shepherd-button-success'
                    });
                }
                
                const stepConfig = {
                    id: `step-${index}`,
                    title: step.title,
                    text: step.text,
                    buttons: buttons,
                    canClickTarget: true,
                    highlightClass: 'tour-highlight-element'
                };
                
                // Если есть привязка к элементу
                if (step.attachTo && step.attachTo.element) {
                    const element = this.getElement(step.attachTo.element);
                    if (element && document.body.contains(element)) {
                        stepConfig.attachTo = {
                            element: element,
                            on: step.attachTo.on || 'bottom'
                        };
                    } else {
                        console.warn('Элемент не найден:', step.attachTo.element);
                        stepConfig.attachTo = false;
                    }
                } else {
                    stepConfig.attachTo = false;
                }
                
                this.tour.addStep(stepConfig);
            });
            
            this.tour.on('complete', () => {
                this.markTourCompleted();
                this.showCompletionMessage();
            });
            
            this.tour.on('cancel', () => {
                this.markTourCompleted();
            });
            
            this.isInitialized = true;
            console.log('Тур создан успешно');
            
        } catch (error) {
            console.error('Ошибка создания тура:', error);
        }
    }
    
    getElement(selector) {
        try {
            // Сначала пробуем обычный селектор
            let element = document.querySelector(selector);
            
            // Если не нашли, пробуем найти по ID
            if (!element && selector.startsWith('#') && selector.length > 1) {
                element = document.getElementById(selector.substring(1));
            }
            
            // Если не нашли, пробуем найти по классу
            if (!element && selector.startsWith('.') && selector.length > 1) {
                element = document.querySelector(selector);
            }
            
            return element;
        } catch (error) {
            console.error('Ошибка поиска элемента:', error);
            return null;
        }
    }
    
    async loadShepherd() {
        return new Promise((resolve, reject) => {
            // Если уже загружен
            if (typeof Shepherd !== 'undefined') {
                resolve();
                return;
            }
            
            // Проверяем, может уже загружается
            if (document.querySelector('script[src*="shepherd"]')) {
                // Ждем загрузки существующего скрипта
                const checkLoad = setInterval(() => {
                    if (typeof Shepherd !== 'undefined') {
                        clearInterval(checkLoad);
                        resolve();
                    }
                }, 100);
                setTimeout(() => {
                    clearInterval(checkLoad);
                    reject(new Error('Timeout loading Shepherd'));
                }, 5000);
                return;
            }
            
            // Загружаем скрипт
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/shepherd.js/10.0.1/shepherd.min.js';
            script.onload = () => {
                console.log('Shepherd.js загружен');
                // Ждем немного для инициализации
                setTimeout(resolve, 100);
            };
            script.onerror = () => {
                console.error('Ошибка загрузки Shepherd.js');
                reject(new Error('Failed to load Shepherd.js'));
            };
            document.head.appendChild(script);
            
            // Загружаем CSS
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://cdnjs.cloudflare.com/ajax/libs/shepherd.js/10.0.1/shepherd.min.css';
            document.head.appendChild(link);
        });
    }
    
    addTourStyles() {
        // Проверяем, не добавлены ли стили уже
        if (document.getElementById('shepherd-custom-styles')) {
            return;
        }
        
        const style = document.createElement('style');
        style.id = 'shepherd-custom-styles';
        style.textContent = `
            /* Основные стили тура */
            .shepherd-element {
                position: fixed !important;
            }
            
            /* Темный оверлей - затемнение всего кроме активного элемента */
            .shepherd-modal-overlay-container {
                z-index: 9999 !important;
                pointer-events: none;
            }
            
            .shepherd-modal-overlay-container path {
                pointer-events: all;
            }
            
            /* Стиль для подсветки активного элемента */
            .tour-highlight-element {
                position: relative;
                z-index: 10001 !important;
                border-radius: 4px;
                box-shadow: 
                    0 0 0 9999px rgba(0, 0, 0, 0.5),
                    0 0 0 3px #667eea,
                    0 0 20px rgba(102, 126, 234, 0.5) !important;
                animation: pulse-border 2s infinite;
            }
            
            /* Анимация пульсации для подсветки */
            @keyframes pulse-border {
                0% {
                    box-shadow: 
                        0 0 0 9999px rgba(0, 0, 0, 0.5),
                        0 0 0 3px #667eea,
                        0 0 20px rgba(102, 126, 234, 0.5);
                }
                50% {
                    box-shadow: 
                        0 0 0 9999px rgba(0, 0, 0, 0.5),
                        0 0 0 5px #764ba2,
                        0 0 30px rgba(118, 75, 162, 0.7);
                }
                100% {
                    box-shadow: 
                        0 0 0 9999px rgba(0, 0, 0, 0.5),
                        0 0 0 3px #667eea,
                        0 0 20px rgba(102, 126, 234, 0.5);
                }
            }
            
            /* Стили самого окна тура */
            .shepherd-theme-arrows.shepherd-element {
                max-width: 400px;
                border-radius: 10px;
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
                border: 2px solid #667eea;
                z-index: 100000;
            }
            
            .shepherd-theme-arrows .shepherd-content {
                border-radius: 10px;
                padding: 0;
                overflow: hidden;
            }
            
            .shepherd-theme-arrows .shepherd-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 15px 20px;
                border-radius: 10px 10px 0 0;
            }
            
            .shepherd-theme-arrows .shepherd-title {
                font-size: 18px;
                font-weight: 600;
                margin: 0;
            }
            
            .shepherd-theme-arrows .shepherd-text {
                font-size: 14px;
                line-height: 1.5;
                padding: 20px;
                color: #333;
                background: #fff;
            }
            
            .shepherd-theme-arrows .shepherd-footer {
                padding: 10px 20px;
                border-top: 1px solid #eee;
                display: flex;
                gap: 10px;
                justify-content: flex-end;
                background: #f8f9fa;
            }
            
            /* Кнопки */
            .shepherd-button-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
                color: white;
                padding: 8px 16px;
                border-radius: 5px;
                cursor: pointer;
                font-weight: 500;
                transition: all 0.3s ease;
            }
            
            .shepherd-button-primary:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            }
            
            .shepherd-button-secondary {
                background: #6c757d;
                border: none;
                color: white;
                padding: 8px 16px;
                border-radius: 5px;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .shepherd-button-secondary:hover {
                background: #5a6268;
                transform: translateY(-1px);
            }
            
            .shepherd-button-skip {
                background: transparent;
                border: 1px solid #dc3545;
                color: #dc3545;
                padding: 8px 16px;
                border-radius: 5px;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .shepherd-button-skip:hover {
                background: #dc3545;
                color: white;
                transform: translateY(-1px);
            }
            
            .shepherd-button-success {
                background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                border: none;
                color: white;
                padding: 8px 16px;
                border-radius: 5px;
                cursor: pointer;
                font-weight: 500;
                transition: all 0.3s ease;
            }
            
            .shepherd-button-success:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
            }
            
            /* Стрелка тура */
            .shepherd-arrow {
                border-color: #667eea transparent transparent;
            }
            
            .shepherd-arrow:before {
                border-color: #667eea transparent transparent;
            }
            
            /* Для разных позиций стрелки */
            .shepherd-element[data-popper-placement^="bottom"] .shepherd-arrow {
                border-color: transparent transparent #667eea;
            }
            
            .shepherd-element[data-popper-placement^="bottom"] .shepherd-arrow:before {
                border-color: transparent transparent #667eea;
            }
            
            .shepherd-element[data-popper-placement^="left"] .shepherd-arrow {
                border-color: transparent transparent transparent #667eea;
            }
            
            .shepherd-element[data-popper-placement^="left"] .shepherd-arrow:before {
                border-color: transparent transparent transparent #667eea;
            }
            
            .shepherd-element[data-popper-placement^="right"] .shepherd-arrow {
                border-color: transparent #667eea transparent transparent;
            }
            
            .shepherd-element[data-popper-placement^="right"] .shepherd-arrow:before {
                border-color: transparent #667eea transparent transparent;
            }
            
            /* Отступы для тура */
            .shepherd-element[data-popper-placement^="right"] {
                margin-left: 10px;
            }
            
            .shepherd-element[data-popper-placement^="left"] {
                margin-right: 10px;
            }
            
            .shepherd-element[data-popper-placement^="top"] {
                margin-bottom: 10px;
            }
            
            .shepherd-element[data-popper-placement^="bottom"] {
                margin-top: 10px;
            }
        `;
        document.head.appendChild(style);
    }
    
    showCompletionMessage() {
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 10001;
            animation: slideIn 0.3s ease;
        `;
        
        // Добавляем анимацию для тоста
        const animationStyle = document.createElement('style');
        animationStyle.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(animationStyle);
        
        toast.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">🎉</span>
                <div>
                    <strong>Обучение завершено!</strong><br>
                    <small>Теперь вы знаете, как работать с этой страницей.</small>
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        
        // Удаляем через 5 секунд с анимацией
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease forwards';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, 5000);
    }
    
    getStepsForPage() {
        const stepsMap = {
            dealsIndex: [
                {
                    attachTo: {
                        element: 'h2 i.fa-file-contract',
                        on: 'bottom'
                    },
                    title: '🏢 Главная страница сделок',
                    text: 'Добро пожаловать! Здесь вы видите все сделки, которые ведете. Это ваш основной рабочий стол.'
                },
                {
                    attachTo: {
                        element: '.card-header.bg-light',
                        on: 'bottom'
                    },
                    title: '🔍 Фильтры и поиск',
                    text: 'Нажмите на эту панель, чтобы открыть фильтры. Вы можете искать сделки по:<br>• ФИО клиента<br>• Марке автомобиля<br>• Номеру договора<br>• Статусу сделки<br>• Дате оплаты'
                },
                {
                    attachTo: {
                        element: 'table.table',
                        on: 'top'
                    },
                    title: '📋 Таблица всех сделок',
                    text: 'Вся информация о ваших сделках в одной таблице:<br><br>1. <strong>№ сделки</strong> - уникальный номер<br>2. <strong>Клиент</strong> - ФИО клиента<br>3. <strong>Автомобиль</strong> - марка и модель<br>4. <strong>Сумма</strong> - общая и оплаченная<br>5. <strong>Статус</strong> - текущее состояние<br>6. <strong>Дата оплаты</strong> - следующий платеж<br>7. <strong>Действия</strong> - кнопки управления'
                },
                {
                    attachTo: {
                        element: 'tbody tr:first-child td:nth-child(5) .badge',
                        on: 'right'
                    },
                    title: '🏷️ Статусы сделок',
                    text: 'Цветные метки показывают состояние сделки:<br><br><span style="background:#6c757d;color:white;padding:2px 6px;border-radius:4px;">Черновик</span> - договор не подписан<br><span style="background:#28a745;color:white;padding:2px 6px;border-radius:4px;">Активна</span> - сделка в работе<br><span style="background:#007bff;color:white;padding:2px 6px;border-radius:4px;">Завершена</span> - все оплачено<br><span style="background:#dc3545;color:white;padding:2px 6px;border-radius:4px;">Просрочена</span> - есть долги'
                },
                {
                    attachTo: {
                        element: '.progress.time-progress',
                        on: 'top'
                    },
                    title: '⏰ Система контроля платежей',
                    text: 'Градиентная полоска - это визуальный таймер до следующего платежа:<br><br>• <span style="color:#28a745">ЗЕЛЕНЫЙ</span> - больше 7 дней до платежа<br>• <span style="color:#ffc107">ЖЕЛТЫЙ</span> - от 1 до 7 дней (внимание!)<br>• <span style="color:#dc3545">КРАСНЫЙ</span> - платеж просрочен<br><br>Под полоской указана точная дата платежа.'
                },
                {
                    attachTo: {
                        element: 'tbody tr:first-child td:last-child',
                        on: 'left'
                    },
                    title: '⚡ Быстрые действия',
                    text: 'Для каждой сделки доступны:<br><br>👁️ <strong>Просмотр</strong> - полная информация<br>✏️ <strong>Редактирование</strong> - изменить данные (кроме активных сделок)<br>🔔 <strong>Напомнить</strong> - отправить SMS клиенту (активно за 2 дня до платежа)<br>📄 <strong>Договор</strong> - загрузить подписанный договор (для черновиков)<br>🗑️ <strong>Удалить</strong> - удалить сделку (только для админов)'
                },
                {
                    attachTo: {
                        element: 'a.btn-primary[href*="create"]',
                        on: 'right'
                    },
                    title: '➕ Создание новой сделки',
                    text: 'Нажмите эту кнопку, чтобы начать оформление новой сделки.<br><br><strong>Для создания потребуется:</strong><br>1. Выбрать клиента из базы<br>2. Выбрать автомобиль<br>3. Заполнить финансовые параметры<br>4. Установить график платежей<br>5. Сгенерировать договор'
                }
            ],

            dealsCreate: [
                {
                    attachTo: {
                        element: '.card-header.bg-green-lt h2',
                        on: 'bottom'
                    },
                    title: '📝 Создание новой сделки',
                    text: 'Здесь вы создаете новый договор лизинга. Заполните все поля внимательно.'
                },
                {
                    attachTo: {
                        element: '#client_id',
                        on: 'right'
                    },
                    title: '👤 Выбор клиента',
                    text: 'Выберите клиента из базы данных.<br><br><strong>Важно:</strong> Если клиента нет в списке, сначала создайте его в разделе "Клиенты".'
                },
                {
                    attachTo: {
                        element: '#car_id',
                        on: 'right'
                    },
                    title: '🚗 Выбор автомобиля',
                    text: 'Выберите автомобиль из доступных вам.<br><br>В списке только свободные автомобили, закрепленные за вами.'
                },
                {
                    attachTo: {
                        element: '#total_amount',
                        on: 'right'
                    },
                    title: '💰 Сумма сделки',
                    text: 'Введите общую сумму сделки.<br><br>Это конечная сумма, которую клиент должен выплатить за весь срок.'
                },
                {
                    attachTo: {
                        element: '#start_date',
                        on: 'top'
                    },
                    title: '📅 Дата начала сделки',
                    text: 'Установите дату начала сделки.<br><br>От этой даты будет рассчитан график платежей.'
                },
                {
                    attachTo: {
                        element: '#payment_period',
                        on: 'right'
                    },
                    title: '📊 Период оплаты',
                    text: 'Выберите периодичность платежей:<br><br>• <strong>Сутки</strong> - ежедневные платежи<br>• <strong>Неделя</strong> - еженедельные<br>• <strong>Месяц</strong> - ежемесячные<br><br>Система автоматически создаст график платежей.'
                },
                {
                    attachTo: {
                        element: 'button[type="submit"]',
                        on: 'top'
                    },
                    title: '💾 Сохранение сделки',
                    text: 'После заполнения всех полей нажмите эту кнопку.<br><br>Система создаст сделку, сгенерирует договор и откроет карточку для дальнейшей работы.'
                }
            ],

            clientsIndex: [
                {
                    attachTo: {
                        element: '.row.mb-4 h2',
                        on: 'bottom'
                    },
                    title: '👥 База клиентов',
                    text: 'Здесь хранится информация обо всех ваших клиентах.'
                },
                {
                    attachTo: {
                        element: 'a.btn-primary[href*="create"]',
                        on: 'right'
                    },
                    title: '➕ Добавить клиента',
                    text: 'Нажмите, чтобы добавить нового клиента в базу.<br><br><strong>Все поля обязательны для заполнения!</strong>'
                },
                {
                    attachTo: {
                        element: 'table.table',
                        on: 'top'
                    },
                    title: '📋 Список клиентов',
                    text: 'Таблица всех клиентов с основной информацией.'
                },
                {
                    attachTo: {
                        element: 'tbody tr:first-child td:last-child',
                        on: 'left'
                    },
                    title: '⚡ Действия с клиентом',
                    text: 'Для каждого клиента доступно:<br><br>👁️ <strong>Просмотр</strong> - полная информация<br>✏️ <strong>Редактирование</strong> - обновить данные<br>🗑️ <strong>Удалить</strong> - удалить клиента (если нет активных сделок)'
                }
            ],

            clientsCreate: [
                {
                    attachTo: {
                        element: '.card-header.bg-green-lt h2',
                        on: 'bottom'
                    },
                    title: '🆕 Создание нового клиента',
                    text: 'Заполните все поля для добавления клиента в базу.'
                },
                {
                    attachTo: {
                        element: '#last_name',
                        on: 'right'
                    },
                    title: '📇 ФИО клиента',
                    text: 'Введите фамилию, имя и отчество клиента.'
                },
                {
                    attachTo: {
                        element: '#passport_series',
                        on: 'right'
                    },
                    title: '📄 Паспортные данные',
                    text: 'Введите серию и номер паспорта.<br><br>Это обязательное поле для всех клиентов.'
                },
                {
                    attachTo: {
                        element: '#phone',
                        on: 'right'
                    },
                    title: '📞 Контактные телефоны',
                    text: 'Основной и дополнительный телефоны.<br><br>На основной телефон будут отправляться SMS-напоминания.'
                },
                {
                    attachTo: {
                        element: 'input[name="passport_main"]',
                        on: 'top'
                    },
                    title: '📎 Загрузка документов',
                    text: 'Загрузите сканы документов:<br>• Паспорт (развороты)<br>• Водительское удостоверение<br>• Другие необходимые документы'
                },
                {
                    attachTo: {
                        element: 'button[type="submit"]',
                        on: 'top'
                    },
                    title: '💾 Сохранение клиента',
                    text: 'После заполнения всех полей нажмите "Сохранить".<br><br>Клиент будет добавлен в базу и станет доступен для выбора в сделках.'
                }
            ],

            clientsShow: [
                {
                    attachTo: {
                        element: '.row.mb-4 h2',
                        on: 'bottom'
                    },
                    title: '👤 Карточка клиента',
                    text: 'Полная информация о клиенте.'
                },
                {
                    attachTo: {
                        element: '.col-md-8 .card.mb-4 .card-body',
                        on: 'right'
                    },
                    title: '📋 Персональные данные',
                    text: 'Все данные клиента, введенные при создании.'
                },
                {
                    attachTo: {
                        element: '.card:has(.card-header:contains("Документы клиента"))',
                        on: 'top'
                    },
                    title: '📎 Документы клиента',
                    text: 'Все загруженные документы.<br><br>Можно просмотреть или скачать любой документ.'
                },
                {
                    attachTo: {
                        element: 'a.btn-warning[href*="edit"]',
                        on: 'right'
                    },
                    title: '✏️ Редактирование',
                    text: 'Нажмите для изменения данных клиента.<br><br>Можно обновить контакты, адреса, загрузить новые документы.'
                }
            ],

            carsIndex: [
                {
                    attachTo: {
                        element: '.row.mb-4 h2',
                        on: 'bottom'
                    },
                    title: '🚗 Автомобили',
                    text: 'Список автомобилей, закрепленных за вами.'
                },
                {
                    attachTo: {
                        element: '.card-header.bg-light',
                        on: 'bottom'
                    },
                    title: '🔍 Фильтрация автомобилей',
                    text: 'Фильтруйте автомобили по:<br>• Статусу (свободен/занят)<br>• Марке<br>• Модели<br>• Гос номеру'
                },
                {
                    attachTo: {
                        element: 'table.table',
                        on: 'top'
                    },
                    title: '📋 Список автомобилей',
                    text: 'Все автомобили с ключевой информацией.'
                },
                {
                    attachTo: {
                        element: 'tbody tr:first-child td:nth-child(5) .badge',
                        on: 'right'
                    },
                    title: '🏷️ Статус автомобиля',
                    text: '<span style="background:#28a745;color:white;padding:2px 6px;border-radius:4px;">Свободен</span> - доступен для сделки<br><span style="background:#6c757d;color:white;padding:2px 6px;border-radius:4px;">Занят</span> - используется в активной сделке'
                },
                {
                    attachTo: {
                        element: 'tbody tr:first-child td:last-child',
                        on: 'left'
                    },
                    title: '⚡ Действия',
                    text: 'Для каждого автомобиля:<br><br>👁️ <strong>Просмотр</strong> - полная информация<br>✏️ <strong>Редактирование</strong> - изменить данные<br>💰 <strong>Расходы</strong> - добавить расходы'
                }
            ],

            carsShow: [
                {
                    attachTo: {
                        element: '.row.mb-4 h2',
                        on: 'bottom'
                    },
                    title: '🚗 Карточка автомобиля',
                    text: 'Полная техническая и финансовая информация об автомобиле.'
                },
                {
                    attachTo: {
                        element: '.col-md-8 .card.mb-4 .card-body',
                        on: 'right'
                    },
                    title: '🔧 Технические характеристики',
                    text: 'Марка, модель, VIN, цвет, гос номер, пробег, топливо.'
                },
                {
                    attachTo: {
                        element: '.nav-tabs',
                        on: 'bottom'
                    },
                    title: '📊 Вкладки документов и расходов',
                    text: 'Переключайтесь между вкладками:<br>• <strong>Документы</strong> - ПТС, СТС, страховки<br>• <strong>Расходы</strong> - ТО, ремонт, заправки'
                },
                {
                    attachTo: {
                        element: 'button[data-bs-target="#addExpenseModal"]',
                        on: 'right'
                    },
                    title: '💸 Учет расходов',
                    text: 'Нажмите "Добавить расход" для учета:<br>• ТО и ремонт<br>• Мойка<br>• Страховка<br>• Прочие расходы<br><br>Все расходы прикрепляются к автомобилю.'
                },
                {
                    attachTo: {
                        element: 'a.btn-outline-success[href*="deals/create"]',
                        on: 'right'
                    },
                    title: '➕ Создать сделку',
                    text: 'Нажмите, чтобы создать сделку с этим автомобилем.<br><br>Доступно только для свободных автомобилей.'
                }
            ],

            dealsShow: [
                {
                    attachTo: {
                        element: '.row.mb-4 h2',
                        on: 'bottom'
                    },
                    title: '📄 Карточка сделки',
                    text: 'Здесь собрана вся информация по конкретной сделке. Вы можете управлять платежами, загружать документы и отслеживать прогресс.'
                },
                {
                    attachTo: {
                        element: '.row.mb-4 .col-md-3 .badge',
                        on: 'right'
                    },
                    title: '🏷️ Статус сделки',
                    text: 'Цветной бейдж показывает текущее состояние сделки:<br><br>• <span style="background:#6c757d;color:white;padding:2px 6px;border-radius:4px;">Черновик</span> - договор не подписан<br>• <span style="background:#28a745;color:white;padding:2px 6px;border-radius:4px;">Активна</span> - сделка в работе<br>• <span style="background:#dc3545;color:white;padding:2px 6px;border-radius:4px;">Просрочена</span> - есть неоплаченные платежи<br>• <span style="background:#007bff;color:white;padding:2px 6px;border-radius:4px;">Завершена</span> - все оплачено'
                },
                {
                    attachTo: {
                        element: '.row.mb-4 .col-md-9 .progress',
                        on: 'top'
                    },
                    title: '📊 Прогресс оплаты',
                    text: 'Прогресс-бар показывает, какая часть общей суммы уже оплачена.<br><br>• Зеленая полоса - оплаченная сумма<br>• Под полосой указаны цифры: оплачено/всего/осталось'
                },
                {
                    attachTo: {
                        element: '.row.mb-4 .col-md-4 .card-body.text-center',
                        on: 'left'
                    },
                    title: '⏰ Следующий платеж',
                    text: 'Здесь отображается информация о ближайшем платеже:<br><br>• Дата следующего платежа<br>• Сколько дней осталось/просрочено<br>• Сумма платежа<br><br>Цвет текста меняется при приближении срока.'
                },
                {
                    attachTo: {
                        element: '.col-md-4 .card.mb-4:first-child .card-body',
                        on: 'right'
                    },
                    title: '👥 Участники сделки',
                    text: 'Основная информация об участниках сделки:<br><br>• <strong>Клиент</strong> - ФИО и телефон (кликабельно)<br>• <strong>Автомобиль</strong> - марка, модель, госномер (кликабельно)<br>• <strong>Менеджер</strong> - ответственный сотрудник'
                },
                {
                    attachTo: {
                        element: '.col-md-4 .card.mb-4:nth-child(2)',
                        on: 'right'
                    },
                    title: '⚙️ Параметры сделки',
                    text: 'Технические параметры сделки:<br><br>• Тип сделки (лизинг/аренда)<br>• Финансовые суммы<br>• Сроки и периодичность<br>• Настройки уведомлений<br><br>Все параметры установлены при создании сделки.'
                },
                {
                    attachTo: {
                        element: '.col-md-4 .card:has(.card-header:contains("Договор"))',
                        on: 'right'
                    },
                    title: '📄 Работа с договором',
                    text: 'Управление договором лизинга:<br><br><strong>Для черновиков:</strong><br>1. Сгенерируйте шаблон договора<br>2. Скачайте и подпишите с клиентом<br>3. Загрузите подписанный договор<br><br><strong>Для активных сделок:</strong><br>• Скачать/просмотреть договор<br>• Проверить дату подписания'
                },
                {
                    attachTo: {
                        element: '.col-md-8 .card.mb-4:first-child .table-responsive',
                        on: 'top'
                    },
                    title: '📋 График платежей',
                    text: 'Полная таблица всех платежей по сделке:<br><br>• <span style="color:#28a745">Зеленые</span> строки - оплаченные платежи<br>• <span style="color:#dc3545">Красные</span> строки - просроченные платежи<br>• Кнопка "Оплатить" для ожидающих платежей<br>• Кнопка "Подробности" для оплаченных'
                },
                {
                    attachTo: {
                        element: 'button[data-bs-target="#registerPaymentModal"]',
                        on: 'right'
                    },
                    title: '💳 Регистрация платежа',
                    text: 'Нажмите кнопку "Оплатить" для регистрации платежа от клиента.<br><br>В модальном окне укажите:<br>• Способ оплаты (нал/безнал)<br>• Номер транзакции (если есть)<br>• Прикрепите чек/документ<br>• Добавьте заметки'
                },
                {
                    attachTo: {
                        element: '.col-md-12 .card:has(.card-body .btn-danger)',
                        on: 'top'
                    },
                    title: '📥 Скачивание графика',
                    text: 'Скачайте официальный график платежей в формате PDF.<br><br>Документ является Приложением №2 к договору и содержит:<br>• Все даты платежей<br>• Суммы платежей<br>• Реквизиты сторон<br><br>Можно скачать или предварительно просмотреть.'
                },
                {
                    attachTo: {
                        element: '.col-md-8 .card:has(.card-header:contains("История уведомлений"))',
                        on: 'top'
                    },
                    title: '📨 История уведомлений',
                    text: 'Все отправленные SMS и email уведомления:<br><br>• Дата и время отправки<br>• Тип уведомления<br>• Статус (отправлено/ошибка)<br>• Краткое содержание<br><br>Внизу указана общая статистика отправок.'
                },
                {
                    attachTo: {
                        element: 'form[action*="send-reminder"] button',
                        on: 'right'
                    },
                    title: '🔔 Ручное напоминание',
                    text: 'Нажмите кнопку "Напомнить", чтобы отправить SMS клиенту вручную.<br><br><strong>Когда использовать:</strong><br>• За 1-2 дня до платежа<br>• Если клиент не отвечает<br>• При просрочке платежа<br><br>Система автоматически подтвердит отправку.'
                },
                {
                    attachTo: {
                        element: 'a.btn-warning[href*="edit"]',
                        on: 'right'
                    },
                    title: '✏️ Редактирование сделки',
                    text: 'Доступно только для сделок в статусе "Черновик".<br><br>Можно изменить:<br>• Параметры сделки<br>• Суммы и сроки<br>• График платежей<br><br><strong>Внимание:</strong> Активные сделки редактируются через регистрацию платежей.'
                },
                {
                    attachTo: {
                        element: '#uploadContractForm',
                        on: 'top'
                    },
                    title: '📤 Загрузка договора',
                    text: '<strong>ВАЖНО:</strong> После подписания договора с клиентом:<br><br>1. Укажите дату подписания<br>2. Загрузите сканированный договор<br>3. Нажмите "Загрузить и активировать сделку"<br><br>Сделка автоматически перейдет в статус "Активна".'
                }
            ]
        };
        
        // Возвращаем шаги для текущей страницы
        const steps = stepsMap[this.currentPage];
        
        // Если шагов нет, возвращаем пустой массив
        if (!steps) {
            console.log('Нет шагов для страницы:', this.currentPage);
            return [];
        }
        
        // Проверяем существование элементов на странице
        const validSteps = steps.filter(step => {
            if (!step.attachTo || !step.attachTo.element) {
                return true; // Шаги без привязки всегда валидны
            }
            
            const element = document.querySelector(step.attachTo.element);
            if (!element) {
                console.warn('Элемент не найден для тура:', step.attachTo.element);
                return false;
            }
            return true;
        });
        
        console.log('Найдено шагов для тура:', validSteps.length);
        return validSteps;
    }
    
    async start() {
        if (!this.shouldShowTour()) {
            console.log('Тур не должен показываться');
            return;
        }
        
        console.log('Запускаем тур для страницы:', this.currentPage);
        
        try {
            // Убеждаемся, что Shepherd загружен
            await this.loadShepherd();
            
            // Добавляем стили
            this.addTourStyles();
            
            // Создаем тур
            this.createTour();
            
            // Запускаем тур
            if (this.tour && this.isInitialized) {
                // Даем время на рендеринг
                setTimeout(() => {
                    try {
                        this.tour.start();
                        console.log('Тур запущен');
                    } catch (error) {
                        console.error('Ошибка запуска тура:', error);
                    }
                }, 100);
            }
            
        } catch (error) {
            console.error('Ошибка инициализации тура:', error);
        }
    }
    
    restart() {
        const pageTourKey = `tourCompleted_${this.currentPage}`;
        localStorage.removeItem(pageTourKey);
        
        if (this.tour) {
            try {
                this.tour.complete();
            } catch (e) {
                // Игнорируем ошибки
            }
            this.tour = null;
        }
        
        this.isInitialized = false;
        this.start();
    }
}

// Остальной код оставляем без изменений
window.showTour = function() {
    console.log('Ручной запуск тура');
    if (window.managerTour) {
        window.managerTour.restart();
    } else {
        window.managerTour = new ManagerTour();
        window.managerTour.start();
    }
};

window.resetPageTour = function() {
    if (window.managerTour && window.managerTour.currentPage) {
        const pageTourKey = `tourCompleted_${window.managerTour.currentPage}`;
        localStorage.removeItem(pageTourKey);
        alert(`✅ Тур для этой страницы сброшен! Нажмите "Запустить тур" для повторного прохождения.`);
    } else {
        alert('❌ Не удалось определить текущую страницу');
    }
};

window.resetAllTours = function() {
    const keys = Object.keys(localStorage);
    let resetCount = 0;
    
    keys.forEach(key => {
        if (key.startsWith('tourCompleted_')) {
            localStorage.removeItem(key);
            resetCount++;
        }
    });
    
    if (resetCount > 0) {
        alert(`✅ Сброшено ${resetCount} туров обучения!\n\nНажмите "Запустить тур" для повторного прохождения.`);
    } else {
        alert('ℹ️ Нет сохраненных туров для сброса.');
    }
};

window.showTourStatus = function() {
    const currentPage = window.managerTour ? window.managerTour.currentPage : 'не определена';
    const keys = Object.keys(localStorage);
    const tourKeys = keys.filter(key => key.startsWith('tourCompleted_'));
    
    let message = `📊 Статус обучения:\n\n`;
    message += `• Текущая страница: ${currentPage}\n`;
    message += `• Пройденных туров: ${tourKeys.length}\n\n`;
    
    if (tourKeys.length > 0) {
        message += `Пройденные страницы:\n`;
        tourKeys.forEach(key => {
            const page = key.replace('tourCompleted_', '');
            message += `  ✓ ${page}\n`;
        });
    } else {
        message += `ℹ️ Вы еще не проходили обучение.`;
    }
    
    alert(message);
};

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM загружен, проверяем тур...');
    
    window.addEventListener('load', function() {
        console.log('Страница полностью загружена');
        
        setTimeout(function() {
            if (window.userRole === 'manager') {
                console.log('Пользователь - менеджер, запускаем тур...');
                window.managerTour = new ManagerTour();
                window.managerTour.start();
            } else {
                console.log('Пользователь не менеджер, тур не запускаем');
            }
        }, 1000);
    }, { once: true });
});

console.log('CRM Тур обучения загружен. Доступные команды:');
console.log('showTour() - запустить тур');
console.log('resetPageTour() - сбросить тур для текущей страницы');
console.log('resetAllTours() - сбросить все туры');
console.log('showTourStatus() - показать статус туров');
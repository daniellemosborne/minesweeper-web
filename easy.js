document.addEventListener('DOMContentLoaded', function() {
    const grid = document.querySelector('.gameGridEasy')
    const flagRemaining = document.querySelector('#flagsRemaining')
    const result = document.querySelector('#result')
    const statsDisplay = document.querySelector('#stats');
    const restartButton = document.querySelector('#restartBtn')
    const boardColorSelect = document.getElementById('boardColor');
    const themeSelect = document.getElementById('theme');
    const width = 5
    let bombAmount = 10
    let squares = []
    let gameOver = false
    let flags = 0
    let clearedSquare = 0
    let timerStarted = false
    let timerInterval
    let timeElapsed = 0


    const backgroundMusic = new Audio('background.mp3')
    backgroundMusic.loop = true
    const loseSound = new Audio('loser.mp3') 
    const winSound = new Audio('winner.mp3')

    const savedBoardColor = localStorage.getItem('boardColor') || 'default';
    const savedTheme = localStorage.getItem('theme') || 'light';
    applyBoardColor(savedBoardColor);
    applyTheme(savedTheme);

    // debugging
    console.log('Initial board color:', savedBoardColor);
    console.log('Initial theme:', savedTheme);

    // board color changes
    boardColorSelect.addEventListener('change', (event) => {
        const selectedColor = event.target.value;
        console.log('Selected board color:', selectedColor);
        applyBoardColor(selectedColor);
        localStorage.setItem('boardColor', selectedColor);
    });

    // theme changes
    themeSelect.addEventListener('change', (event) => {
        const selectedTheme = event.target.value;
        console.log('Selected theme:', selectedTheme);
        applyTheme(selectedTheme);
        localStorage.setItem('theme', selectedTheme);
    });

    // apply board color
    function applyBoardColor(color) {
        console.log('Applying board color:', color);
        grid.className = `gameGridEasy ${color}`;
    }

    // apply theme
    function applyTheme(theme) {
        console.log('Applying theme:', theme);
        document.body.className = `${theme}-theme`;
    }

    // initialize stats in localStorage if not present
    if (!localStorage.getItem('gamesPlayed')) localStorage.setItem('gamesPlayed', 0);
    if (!localStorage.getItem('gamesWon')) localStorage.setItem('gamesWon', 0);
    if (!localStorage.getItem('timePlayed')) localStorage.setItem('timePlayed', 0);

    // load stats from db
    function loadStats() {
        console.log('loadStats called'); // debug
        statsDisplay.innerHTML = `<p>Loading stats...</p>`;
        fetch('gamestats.php')
            .then((response) => {
                console.log('Response received:', response.status); // debug
                return response.json();
            })
            .then((data) => {
                console.log('Response data:', data); // debug
                if (data.success) {
                    statsDisplay.innerHTML = `
                        <p>Games Played: ${data.gamesPlayed}</p>
                        <p>Games Won: ${data.gamesWon}</p>
                        <p>Total Time Played: ${formatTime(data.timePlayed)}</p>
                    `;
                } else {
                    statsDisplay.innerHTML = `<p>Error loading stats.</p>`;
                    console.error(data.message);
                }
            })
            .catch((error) => console.error('Error fetching stats:', error));
    }

    //format time
    function formatTime(seconds) {
        const hrs = Math.floor(seconds / 3600);
        const mins = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        return `${hrs}h ${mins}m ${secs}s`;
    }

    // update stats in db
    function updateStats(won) {
        console.log('updateStats called with:', { won, timePlayed: timeElapsed }); // debug
        fetch('gamestats.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({
                won: won,
                timePlayed: timeElapsed,
            }),
        })
            .then((response) => {
                console.log('Server response:', response.status); // debug
                return response.json();
            })
            .then((data) => {
                console.log('Server data:', data); //debug
            })
            .catch((error) => console.error('Error updating stats:', error));
    }

    function startTimer() {
        const timerDisplay = document.querySelector('#timer') 
        timerInterval = setInterval(() => {
            timeElapsed++
            timerDisplay.innerHTML = `Time: ${timeElapsed} sec`
        }, 1000)

        // start background music
        backgroundMusic.play();
    }
    
    function stopTimer() {
        clearInterval(timerInterval)
        backgroundMusic.pause();
        backgroundMusic.currentTime = 0;
    }

    //creating the game board
    function createGameBoard() {    
        flagRemaining.innerHTML = bombAmount
        gameOver = false;
        flags = 0;
        clearedSquare = 0;
        squares = [];
        timeElapsed = 0; // reset time
        timerStarted = false;

        // reset music
        backgroundMusic.currentTime = 0;

        //randomizing the bombs in the grid
        const bombs = Array(bombAmount).fill('bomb')
        const safeSpace = Array(width * width - bombAmount).fill('safe')
        const placements = safeSpace.concat(bombs)
        const randomizingSpaces = placements.sort(() => Math.random() - 0.5)

        grid.innerHTML = '';

        for (let i = 0; i < width * width; i++) {
            const square = document.createElement('div')
            square.id = i
            square.classList.add(randomizingSpaces[i])
            grid.appendChild(square)
            squares.push(square)

            //normal clicks
            square.addEventListener('click', function() {
                if (!timerStarted) {
                    startTimer();
                    timerStarted = true;
                }
                click(square)   
            })

            //left clicks
            square.addEventListener('contextmenu', function(e) {
                if (!timerStarted) {
                    startTimer();
                    timerStarted = true;
                }
                e.preventDefault()
                addFlag(square)
            })
        }

        //adding numbers to the squares
        for(let i = 0; i <squares.length; i++) {
            let total = 0
            const isLeftCorner = (i % width === 0)
            const isRightCorner = (i % width === width - 1)

            if (squares[i].classList.contains('safe')) {
                if (i > 0 && !isLeftCorner && squares[i - 1].classList.contains('bomb')) total++; // left
                if (i > width - 1 && !isRightCorner && squares[i + 1 - width].classList.contains('bomb')) total++; // top-right
                if (i > width - 1 && squares[i - width].classList.contains('bomb')) total++; // top
                if (i > width && !isLeftCorner && squares[i - width - 1].classList.contains('bomb')) total++; // top-left
                if (i < width * width - 1 && !isRightCorner && squares[i + 1].classList.contains('bomb')) total++; // right
                if (i < width * (width - 1) && !isLeftCorner && squares[i - 1 + width].classList.contains('bomb')) total++; // bottom-left
                if (i < width * (width - 1) && !isRightCorner && squares[i + 1 + width].classList.contains('bomb')) total++; // bottom-right
                if (i < width * (width - 1) && squares[i + width].classList.contains('bomb')) total++; // bottom
                squares[i].setAttribute('data',total)
            }
        }
    }

    createGameBoard()
    loadStats()

    function addFlag(square) {
        if (gameOver) return
        if (!square.classList.contains('checked') && (flags < bombAmount)) {
            if(!square.classList.contains('flag')) {
                square.classList.add('flag')
                flags++
                square.innerHTML = '🐾'
                flagRemaining.innerHTML = bombAmount - flags
                winnerWinner()
            }
            else {
                square.classList.remove('flag')
                flags--
                square.innerHTML = ''
                flagRemaining.innerHTML = bombAmount - flags

            }
        }
    }

    function click(square) {
        if (gameOver || square.classList.contains('checked') || square.classList.contains('flagged')) return

        if (square.classList.contains('bomb')) {
            stopTimer()
            isGameOver()
        }
        else {
            let total = square.getAttribute('data')
            if (total != 0) {
                if (total == 1) square.classList.add('one')
                if (total == 2) square.classList.add('two')
                if (total == 3) square.classList.add('three')
                if (total == 4) square.classList.add('four')
                square.innerHTML = total
                if (!square.dataset.cleared) {
                   clearedSquare++
                   square.dataset.cleared = true
                }
                winnerDinner()
                return
            }
            checkedSquare(square)
        }
        if (!square.classList.contains('checked')) {
            square.classList.add('checked')
            if (!square.dataset.cleared) {
                clearedSquare++
                square.dataset.cleared = true
            }
        winnerDinner()
        }
    }

    function checkedSquare(square) {
        const currentId = square.id
        const isLeftCorner = (currentId % width === 0)
        const isRightCorner = (currentId % width === width - 1)

        setTimeout(() => {
            if (currentId > 0 && !isLeft) click(squares[currentId - 1]); // left
            if (currentId > width - 1 && !isRightCorner) click(squares[currentId + 1 - width]); // top-right
            if (currentId > width - 1) click(squares[currentId - width]); // top
            if (currentId > width && !isLeftCorner) click(squares[currentId - 1 - width]); // top-left
            if (currentId < width * width - 1 && !isRightCorner) click(squares[currentId + 1]); // right
            if (currentId < width * (width - 1) && !isLeftCorner) click(squares[currentId - 1 + width]); // bottom-left
            if (currentId < width * (width - 1) && !isRightCorner) click(squares[currentId + 1 + width]); // bottom-right
            if (currentId < width * (width - 1)) click(squares[currentId + width]); // bottom
        }, 10);
    }

    function winnerDinner() {
        const safeSquares = width * width - bombAmount
        if (clearedSquare == safeSquares) {
            result.innerHTML = 'WINNER WINNER CHICKEN DINNER';
            gameOver = true;
            stopTimer()
            restartButton.style.display = 'block'; 
            // play win sound
            winSound.play()
            updateStats(true)
        }
    }


    function winnerWinner() {
        let matches = 0

        for (let i = 0; i <squares.length; i++) {
            if (squares[i].classList.contains('flag') && squares[i].classList.contains('bomb')){
                matches++
            }
            if (matches === bombAmount) {
                result.innerHTML = 'WINNER WINNER CHICKEN DINNER'
                gameOver = true
                stopTimer()
                restartButton.style.display = 'block'; 
                // play win sound
                winSound.play()
                updateStats(true)
            }
        }
            
    }


    function isGameOver() {
        result.innerHTML = 'WOOF LUCK! GAME OVER'
        gameOver = true
        stopTimer()
        loseSound.play();

        //show all dogs
        squares.forEach(function(square){
            if (square.classList.contains('bomb')) {
                square.innerHTML = '🐶'
                square.classList.remove('bomb')
                square.classList.add('checked')
            }
        })
     // show restart button
        restartButton.style.display = 'block';
        updateStats(false)
    }


    // restart game
    restartButton.addEventListener('click', function() {
        restartButton.style.display = 'none'; // hide the restart button
        result.innerHTML = '';
        createGameBoard(); // recreate the game board
    });
    
})
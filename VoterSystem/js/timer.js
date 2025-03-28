function setVotingTime() {
    let minutes = document.getElementById("timerInput").value;
    if (!minutes || minutes <= 0) {
        alert("Please enter a valid number of minutes.");
        return;
    }

    let endTime = new Date().getTime() + minutes * 60000; // Calculate end time
    localStorage.setItem("votingEndTime", endTime);
    
    alert("Voting timer set successfully!");

    updateTimerDisplay();
}

function updateTimerDisplay() {
    let endTime = localStorage.getItem("votingEndTime");
    if (endTime) {
        let remaining = endTime - new Date().getTime();
        if (remaining > 0) {
            document.getElementById("timerDisplay").innerText =
                Math.floor(remaining / 60000) + "m " + Math.floor((remaining % 60000) / 1000) + "s";
            setTimeout(updateTimerDisplay, 1000);
        } else {
            document.getElementById("timerDisplay").innerText = "Voting Ended";
        }
    } else {
        document.getElementById("timerDisplay").innerText = "--";
    }
}

updateTimerDisplay(); // Start displaying time immediately

function getBotResponse(input) {
    //rock paper scissors
    if (input == "rock") {
        return "paper";
    } else if (input == "paper") {
        return "scissors";
    } else if (input == "scissors") {
        return "rock";
    }

    // Simple responses
    if (input.toLowerCase().includes("bonjour".toLowerCase())) {
        return "Bonjour , Artisanat Help center !";
    } else if (input == "goodbye") {
        return "Talk to you later!";
    } else {
        return "Nous vous comprenons pas , veuillez essayer une autre question!";
    }
}

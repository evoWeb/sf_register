interface Verdict {
  score: number,
  log: string,
  verdict?: string
}

export default class PasswordStrengthCalculator {
  /**
   * password length:
   * level 0 (0 point): less than 4 characters
   * level 1 (6 points): between 5 and 7 characters
   * level 2 (12 points): between 8 and 15 characters
   * level 3 (18 points): 16 or more characters
   */
  verdictLength(password: String): Verdict {
    let score = 0,
      log = '',
      length = password.length;
    switch (true) {
      case length > 0 && length < 5:
        log = '3 points for length (' + length + ')';
        score = 3;
        break;

      case length > 4 && length < 8:
        log = '6 points for length (' + length + ')';
        score = 6;
        break;

      case length > 7 && length < 16:
        log = '12 points for length (' + length + ')';
        score = 12;
        break;

      case length > 15:
        log = '18 points for length (' + length + ')';
        score = 18;
        break;
    }
    return {score: score, log: log};
  };

  /**
   * letters:
   * level 0 (0 points): no letters
   * level 1 (5 points): all letters are lower case
   * level 1 (5 points): all letters are upper case
   * level 2 (7 points): letters are mixed case
   */
  verdictLetter(password: String): Verdict {
    let score = 0,
      log = '',
      matchLower = password.match(/[a-z]/),
      matchUpper = password.match(/[A-Z]/);
    if (matchLower) {
      if (matchUpper) {
        score = 7;
        log = '7 points for letters are mixed';
      } else {
        score = 5;
        log = '5 point for at least one lower case char';
      }
    } else if (matchUpper) {
      score = 5;
      log = '5 points for at least one upper case char';
    }
    return {score: score, log: log};
  };

  /**
   * numbers:
   * level 0 (0 points): no numbers exist
   * level 1 (5 points): one number exists
   * level 1 (7 points): 3 or more numbers exists
   */
  verdictNumbers(password: String): Verdict {
    let score = 0,
      log = '',
      numbers = password.replace(/\D/gi, '');
    if (numbers.length > 1) {
      score = 7;
      log = '7 points for at least three numbers';
    } else if (numbers.length > 0) {
      score = 5;
      log = '5 points for at least one number';
    }
    return {score: score, log: log};
  };

  /**
   * special characters:
   * level 0 (0 points): no special characters
   * level 1 (5 points): one special character exists
   * level 2 (10 points): more than one special character exists
   */
  verdictSpecialChars(password: String): Verdict {
    let score = 0,
      log = '',
      specialCharacters = password.replace(/[\w\s]/gi, '');
    if (specialCharacters.length > 1) {
      score = 10;
      log = '10 points for at least two special chars';
    } else if (specialCharacters.length > 0) {
      score = 5;
      log = '5 points for at least one special char';
    }
    return {score: score, log: log};
  };

  /**
   * combinations:
   * level 0 (1 points): mixed case letters
   * level 0 (1 points): letters and numbers
   * level 1 (2 points): mixed case letters and numbers
   * level 3 (4 points): letters, numbers and special characters
   * level 4 (6 points): mixed case letters, numbers and special characters
   */
  verdictCombos(letter: number, number: number, special: number): Verdict {
    let score = 0,
      log = '';
    if (letter === 7 && number > 0 && special > 0) {
      score = 6;
      log = '6 combo points for letters, numbers and special characters';
    } else if (letter > 0 && number > 0 && special > 0) {
      score = 4;
      log = '4 combo points for letters, numbers and special characters';
    } else if (letter === 7 && number > 0) {
      score = 2;
      log = '2 combo points for mixed case letters and numbers';
    } else if (letter > 0 && number > 0) {
      score = 1;
      log = '1 combo points for letters and numbers';
    } else if (letter === 7) {
      score = 1;
      log = '1 combo points for mixed case letters';
    }
    return {score: score, log: log};
  };

  /**
   * final verdict base on final score
   */
  finalVerdict(finalScore: number): string {
    let strVerdict = '';
    if (finalScore < 16) {
      strVerdict = 'very weak';
    } else if (finalScore > 15 && finalScore < 25) {
      strVerdict = 'weak';
    } else if (finalScore > 24 && finalScore < 35) {
      strVerdict = 'mediocre';
    } else if (finalScore > 34 && finalScore < 45) {
      strVerdict = 'strong';
    } else {
      strVerdict = 'stronger';
    }
    return strVerdict;
  };

  calculate(password: string): Verdict {
    let lengthVerdict = this.verdictLength(password);
    let letterVerdict = this.verdictLetter(password);
    let numberVerdict = this.verdictNumbers(password);
    let specialVerdict = this.verdictSpecialChars(password);
    let combosVerdict = this.verdictCombos(letterVerdict.score, numberVerdict.score, specialVerdict.score);

    let score =
      lengthVerdict.score
      + letterVerdict.score
      + numberVerdict.score
      + specialVerdict.score
      + combosVerdict.score;

    let log = [
      lengthVerdict.log,
      letterVerdict.log,
      numberVerdict.log,
      specialVerdict.log,
      combosVerdict.log,
      score + ' points final score'
    ].join("\n");

    return {score: score, verdict: this.finalVerdict(score), log: log};
  }
}

export function validHead(head) {
    return head.length > 0 && head.length <= 80;
}

export function validBody(body) {
    return body.length > 0 && body.length <= 280;
}

export function validEmail(email) {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    return emailPattern.test(email);
}

export function validUsername(username) {
    const usernamePattern = /^[a-zA-Z0-9_]{4,28}$/;

    return usernamePattern.test(username);
}

export function validPassword(password) {
    const lengthPattern = /^.{8,32}$/;
    const capitalLetterPattern = /[A-Z]/;
    const specialCharacterPattern = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/;

    return (
        lengthPattern.test(password) &&
        capitalLetterPattern.test(password) &&
        specialCharacterPattern.test(password)
    );
}

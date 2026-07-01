const TOKEN_KEY = "admin_token";
const ADMIN_KEY = "admin_user";

export function saveToken(token: string) {
  localStorage.setItem(TOKEN_KEY, token);
}

export function getToken() {
  return localStorage.getItem(TOKEN_KEY);
}

export function removeToken() {
  localStorage.removeItem(TOKEN_KEY);
}

export function saveAdmin(admin: { full_name: string; email: string }) {
  localStorage.setItem(ADMIN_KEY, JSON.stringify(admin));
}

export function getAdmin() {
  const admin = localStorage.getItem(ADMIN_KEY);

  if (!admin) {
    return null;
  }

  return JSON.parse(admin);
}

export function removeAdmin() {
  localStorage.removeItem(ADMIN_KEY);
}

import axios from "axios";
import api from "./api";
import type {
  LoginCredentials,
  LoginResponse,
  ApiErrorResponse,
} from "../types/login";

const USE_MOCK_AUTH = true;

export async function loginAdmin(
  credentials: LoginCredentials,
): Promise<LoginResponse> {
  // mock data to test
  if (USE_MOCK_AUTH) {
    await new Promise((resolve) => setTimeout(resolve, 900));

    if (!credentials.email.trim() || !credentials.password.trim()) {
      throw new Error("Please enter your email and password.");
    }

    return {
      data: {
        full_name: "Admin User",
        email: credentials.email,
        token: "fake-admin-token",
      },
      message: "Login successful.",
      status: 200,
    };
  }

  try {
    const formData = new FormData();

    formData.append("email", credentials.email);
    formData.append("password", credentials.password);

    const response = await api.post<LoginResponse>("login", formData);

    return response.data;
  } catch (error) {
    if (axios.isAxiosError<ApiErrorResponse>(error)) {
      const responseMessage = error.response?.data?.message;

      if (responseMessage) {
        throw new Error(responseMessage, { cause: error });
      }

      if (error.message === "Network Error") {
        throw new Error("Server is not available right now.", {
          cause: error,
        });
      }

      throw new Error("Login failed. Please try again.", { cause: error });
    }

    throw new Error("Something went wrong.", { cause: error });
  }
}

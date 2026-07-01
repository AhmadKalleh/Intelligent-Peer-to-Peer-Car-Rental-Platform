export type LoginCredentials = {
  email: string;
  password: string;
};

export type LoginResponse = {
  data: {
    full_name: string;
    email: string;
    token: string;
  };
  message: string;
  status: number;
};

export type ApiErrorResponse = {
  data: [];
  message: string;
  status: number;
};

export type stateType = {
  isLoading: boolean;
  errorMessage: string;
};

export type actionType =
  | { type: "LOADING" }
  | { type: "SUCCESS" }
  | { type: "ERROR"; payload: string };

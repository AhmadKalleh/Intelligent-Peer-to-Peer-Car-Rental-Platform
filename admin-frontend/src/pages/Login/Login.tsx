import { useReducer, useState } from "react";
import { useNavigate } from "react-router-dom";
import Loader from "../../components/Loader/Loader";
import { loginAdmin } from "../../services/loginService";
import { saveAdmin, saveToken } from "../../utils/storage";
import { Mail, Lock, ArrowRight } from "lucide-react";
import styles from "./Login.module.css";
import type { actionType, stateType } from "../../types/login";

function Login() {
  const navigate = useNavigate();

  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");

  const initState = {
    isLoading: false,
    errorMessage: "",
  };

  const reducer = (state: stateType, action: actionType) => {
    switch (action.type) {
      case "SUCCESS":
        return {
          ...state,
          isLoading: false,
          errorMessage: "",
        };
      case "LOADING":
        return {
          ...state,
          isLoading: true,
          errorMessage: "",
        };
      case "ERROR":
        return {
          ...state,
          isLoading: false,
          errorMessage: action.payload,
        };
      default:
        return state;
    }
  };

  const [state, dispatch] = useReducer(reducer, initState);

  async function handleSubmit(event: React.SubmitEvent<HTMLFormElement>) {
    event.preventDefault();

    try {
      dispatch({ type: "LOADING" });

      const response = await loginAdmin({
        email,
        password,
      });

      saveToken(response.data.token);
      saveAdmin({
        full_name: response.data.full_name,
        email: response.data.email,
      });

      navigate("/dashboard");
    } catch (error) {
      if (error instanceof Error) {
        dispatch({
          type: "ERROR",
          payload: error.message,
        });
      } else {
        dispatch({
          type: "ERROR",
          payload: "Something went wrong.",
        });
      }
    }
  }

  return (
    <main className={styles.loginPage}>
      <section className={styles.loginShell}>
        <div className={styles.brandPanel}>
          <header className={styles.header}>
            <h1>SYRO</h1>
            <p>ADMIN PORTAL</p>
          </header>

          <div className={styles.brandContent}>
            <h2>Secure Car Rental Control Center</h2>
            <p>
              Manage users, review car upload requests, and handle complaints
              from one protected admin workspace.
            </p>
          </div>
        </div>

        <div className={styles.formPanel}>
          <form className={styles.form} onSubmit={handleSubmit}>
            <div className={styles.formGroup}>
              <label htmlFor="email">EMAIL ADDRESS</label>

              <div className={styles.inputWrapper}>
                <Mail size={22} />
                <input
                  id="email"
                  type="email"
                  placeholder="Enter admin email"
                  value={email}
                  onChange={(event) => setEmail(event.target.value)}
                />
              </div>
            </div>

            <div className={styles.formGroup}>
              <label htmlFor="password">PASSWORD</label>

              <div className={styles.inputWrapper}>
                <Lock size={22} />
                <input
                  id="password"
                  type="password"
                  placeholder="Enter admin password"
                  value={password}
                  onChange={(event) => setPassword(event.target.value)}
                />
              </div>
            </div>

            {state.errorMessage && (
              <p className={styles.error}>{state.errorMessage}</p>
            )}

            <button
              type="submit"
              className={styles.loginButton}
              disabled={state.isLoading}
            >
              {state.isLoading ? (
                <Loader size="small" color="#093340" />
              ) : (
                <>
                  Submit Login <ArrowRight size={22} />
                </>
              )}
            </button>
          </form>
        </div>
      </section>
    </main>
  );
}

export default Login;

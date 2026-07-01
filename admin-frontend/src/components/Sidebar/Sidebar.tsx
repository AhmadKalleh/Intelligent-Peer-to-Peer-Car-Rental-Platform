import {
  BarChart3,
  Users,
  Car,
  MessageSquareText,
  UserCircle,
  LogOut,
} from "lucide-react";
import { NavLink, useNavigate } from "react-router-dom";
import styles from "./Sidebar.module.css";
import syroLogo from "../../assets/images/syro-logo.png";

const navItems = [
  {
    path: "/dashboard",
    label: "Statistics",
    icon: BarChart3,
  },
  {
    path: "/users",
    label: "User Management",
    icon: Users,
  },
  {
    path: "/car-verification",
    label: "Car Verification",
    icon: Car,
  },
  {
    path: "/complaints",
    label: "Complaints",
    icon: MessageSquareText,
  },
  {
    path: "/profile",
    label: "Profile",
    icon: UserCircle,
  },
];

function Sidebar() {
  const navigate = useNavigate();

  function handleLogout() {
    localStorage.removeItem("admin_token");
    localStorage.removeItem("admin_user");
    navigate("/login");
  }

  return (
    <aside className={styles.sidebar}>
      <div>
        <div className={styles.brand}>
          <div className={styles.brandIcon}>
            <img className={styles.logoImage} src={syroLogo} alt="SYRO Logo" />
          </div>

          <div>
            <h2>SYRO</h2>
            <p>ADMIN PANEL</p>
          </div>
        </div>

        <nav className={styles.nav}>
          <ul>
            {navItems.map((item) => {
              const Icon = item.icon;

              return (
                <li key={item.path}>
                  <NavLink
                    to={item.path}
                    className={({ isActive }) =>
                      isActive
                        ? `${styles.navLink} ${styles.active}`
                        : styles.navLink
                    }
                  >
                    <Icon size={21} />
                    <span>{item.label}</span>
                  </NavLink>
                </li>
              );
            })}
          </ul>
        </nav>
      </div>

      <button
        type="button"
        className={styles.logoutButton}
        onClick={handleLogout}
      >
        <LogOut size={20} />
        <span>Logout</span>
      </button>
    </aside>
  );
}

export default Sidebar;

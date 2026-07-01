import { Outlet } from "react-router-dom";
import Sidebar from "../components/Sidebar/Sidebar";
import styles from "./DashboardLayout.module.css";

function DashboardLayout() {
  return (
    <div className={styles.dashboardLayout}>
      <Sidebar />

      <main className={styles.mainContent}>
        <Outlet />
      </main>
    </div>
  );
}

export default DashboardLayout;

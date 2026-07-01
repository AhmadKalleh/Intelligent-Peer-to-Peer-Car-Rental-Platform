import { Navigate, Route, Routes } from "react-router-dom";

import Login from "../pages/Login/Login";
import DashboardLayout from "../layouts/DashboardLayout";
import ProtectedRoute from "./ProtectedRoute";

import Dashboard from "../pages/Dashboard/Dashboard";
import Users from "../pages/Users/Users";
import CarRequests from "../pages/CarVerification/CarVerification";
import Complaints from "../pages/Complaints/Complaints";
import Profile from "../pages/Profile/Profile";

function AppRoutes() {
  return (
    <Routes>
      <Route path="/" element={<Navigate to="/login" replace />} />
      <Route path="/login" element={<Login />} />

      <Route element={<ProtectedRoute />}>
        <Route element={<DashboardLayout />}>
          <Route path="/dashboard" element={<Dashboard />} />
          <Route path="/users" element={<Users />} />
          <Route path="/car-verification" element={<CarRequests />} />
          <Route path="/complaints" element={<Complaints />} />
          <Route path="/profile" element={<Profile />} />
        </Route>
      </Route>
    </Routes>
  );
}

export default AppRoutes;

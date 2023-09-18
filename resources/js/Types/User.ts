import type Admin from "./Admin";
import type Company from "./Company";
import type Model from "./Model";
import type Student from "./Student";

interface BaseUser extends Model {
    current_team_id?: number;
    email_verified_at?: string;
    email: string;
    name: string;
    profile_photo_path?: string;
    profile_photo_url: string;
    two_factor_confirmed_at?: string;
    two_factor_enabled: boolean;
    usertype_id: number;
}

interface StudentUser extends BaseUser {
    usertype_type: "App\\Models\\Student";
    usertype?: Student;
}

interface CompanyUser extends BaseUser {
    usertype_type: "App\\Models\\Company";
    usertype?: Company;
}

interface AdminUser extends BaseUser {
    usertype_type: "App\\Models\\Admin";
    usertype?: Admin;
}

export type User = StudentUser | CompanyUser | AdminUser;

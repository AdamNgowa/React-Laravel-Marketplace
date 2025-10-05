import { Link, useForm, usePage } from "@inertiajs/react";
import MiniCartDropdown from "./MiniCartDropdown";
import { FormEventHandler, useState } from "react";
import { PageProps } from "@/types";
import {
  MagnifyingGlassIcon,
  Bars3Icon,
  XMarkIcon,
} from "@heroicons/react/24/outline";

function NavBar() {
  const { auth, departments, keyword } = usePage<PageProps>().props;
  const { user } = auth;

  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  const searchForm = useForm<{ keyword: string }>({
    keyword: keyword || "",
  });

  const { url } = usePage();

  const onSubmit: FormEventHandler = (e) => {
    e.preventDefault();
    searchForm.get(url, {
      preserveScroll: true,
      preserveState: true,
    });
  };

  return (
    <>
      {/* Top Navbar */}
      <div className="navbar bg-base-100 shadow-sm sticky top-0 z-50">
        {/* Left: Brand + Mobile Menu Button */}
        <div className="flex-1">
          <div className="flex items-center gap-2">
            <button
              className="btn btn-ghost lg:hidden"
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
              aria-label="Toggle menu"
            >
              {mobileMenuOpen ? (
                <XMarkIcon className="w-5 h-5" />
              ) : (
                <Bars3Icon className="w-5 h-5" />
              )}
            </button>

            <Link href="/" className="btn btn-ghost text-xl">
              LaraStore
            </Link>
          </div>
        </div>

        {/* Center: Search Bar */}
        <div className="hidden sm:flex flex-1 justify-center">
          <form
            onSubmit={onSubmit}
            className="join w-full max-w-md md:max-w-lg"
          >
            <input
              value={searchForm.data.keyword}
              onChange={(e) => searchForm.setData("keyword", e.target.value)}
              className="input input-bordered join-item w-full"
              placeholder="Search"
            />
            <button type="submit" className="btn join-item">
              <MagnifyingGlassIcon className="w-4 h-4" />
            </button>
          </form>
        </div>

        {/* Right: User & Cart */}
        <div className="flex-none flex items-center gap-3">
          <MiniCartDropdown />

          {user ? (
            <div className="dropdown dropdown-end">
              <button
                tabIndex={0}
                className="btn btn-primary btn-sm normal-case"
              >
                {user.name}
              </button>
              <ul
                tabIndex={0}
                className="menu menu-sm dropdown-content bg-base-100 rounded-box z-20 mt-3 w-52 p-2 shadow"
              >
                <li>
                  <Link href={route("profile.update")}>Profile</Link>
                </li>
                <li>
                  <Link href={route("logout")} as="button" method="post">
                    Logout
                  </Link>
                </li>
              </ul>
            </div>
          ) : (
            <div className="hidden sm:flex gap-2">
              <Link href={route("login")} className="btn btn-ghost">
                Login
              </Link>
              <Link href={route("register")} className="btn btn-primary">
                Register
              </Link>
            </div>
          )}
        </div>
      </div>

      {/* Search bar (for mobile only) */}
      <div className="sm:hidden bg-base-100 border-t px-3 py-2">
        <form onSubmit={onSubmit} className="join w-full">
          <input
            value={searchForm.data.keyword}
            onChange={(e) => searchForm.setData("keyword", e.target.value)}
            className="input input-bordered join-item w-full"
            placeholder="Search"
          />
          <button type="submit" className="btn join-item">
            <MagnifyingGlassIcon className="w-4 h-4" />
          </button>
        </form>
      </div>

      {/* Department Menu */}
      <div
        className={`overflow-hidden bg-base-100 border-t transition-all duration-300 lg:overflow-visible lg:max-h-none lg:border-t-0 ${
          mobileMenuOpen ? "max-h-96 py-2" : "max-h-0 py-0"
        }`}
      >
        <div className="flex justify-center">
          <ul className="menu menu-horizontal flex-col lg:flex-row px-1 gap-1 lg:gap-2 text-center w-full lg:w-auto">
            {departments.map((department) => (
              <li key={department.id}>
                <Link
                  href={route("product.byDepartment", department.slug)}
                  onClick={() => setMobileMenuOpen(false)}
                >
                  {department.name}
                </Link>
              </li>
            ))}
          </ul>
        </div>
      </div>
    </>
  );
}

export default NavBar;

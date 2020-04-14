SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `monitor`
--

-- --------------------------------------------------------

--
-- Table structure for table `dev`
--

CREATE TABLE `dev` (
  `sid` varchar(8) NOT NULL,
  `addr` varchar(16) NOT NULL,
  `conn` varchar(16) NOT NULL,
  `host` varchar(32) NOT NULL,
  `last` mediumtext NOT NULL,
  `sign` bigint(12) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `disk`
--

CREATE TABLE `disk` (
  `uid` varchar(8) NOT NULL,
  `used` bigint(12) UNSIGNED NOT NULL,
  `avail` bigint(12) UNSIGNED NOT NULL,
  `iused` bigint(12) UNSIGNED NOT NULL,
  `ifree` bigint(12) UNSIGNED NOT NULL,
  `sign` bigint(12) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `inet`
--

CREATE TABLE `inet` (
  `uid` varchar(8) NOT NULL,
  `txin` bigint(12) UNSIGNED NOT NULL,
  `txinrate` bigint(12) UNSIGNED NOT NULL,
  `txout` bigint(12) UNSIGNED NOT NULL,
  `txoutrate` bigint(12) UNSIGNED NOT NULL,
  `timestamp` bigint(12) UNSIGNED NOT NULL,
  `sign` bigint(12) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `kern`
--

CREATE TABLE `kern` (
  `uid` varchar(8) NOT NULL,
  `pid` bigint(12) UNSIGNED NOT NULL,
  `file` bigint(12) UNSIGNED NOT NULL,
  `filemax` bigint(12) UNSIGNED NOT NULL,
  `sock` bigint(12) UNSIGNED NOT NULL,
  `sockmax` bigint(12) UNSIGNED NOT NULL,
  `proc` bigint(12) UNSIGNED NOT NULL,
  `procmax` bigint(12) UNSIGNED NOT NULL,
  `conn` bigint(12) UNSIGNED NOT NULL,
  `connmax` bigint(12) UNSIGNED NOT NULL,
  `sign` bigint(12) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `load`
--

CREATE TABLE `load` (
  `uid` varchar(8) NOT NULL,
  `1m` decimal(5,2) UNSIGNED NOT NULL,
  `5m` decimal(5,2) UNSIGNED NOT NULL,
  `15m` decimal(5,2) UNSIGNED NOT NULL,
  `sign` bigint(12) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `mem`
--

CREATE TABLE `mem` (
  `uid` varchar(8) NOT NULL,
  `active` bigint(12) UNSIGNED NOT NULL,
  `inact` bigint(12) UNSIGNED NOT NULL,
  `laundry` bigint(12) UNSIGNED NOT NULL,
  `wired` bigint(12) UNSIGNED NOT NULL,
  `buf` bigint(12) UNSIGNED NOT NULL,
  `free` bigint(12) UNSIGNED NOT NULL,
  `sign` bigint(12) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `swap`
--

CREATE TABLE `swap` (
  `uid` varchar(8) NOT NULL,
  `total` bigint(12) UNSIGNED NOT NULL,
  `used` bigint(12) UNSIGNED NOT NULL,
  `free` bigint(12) UNSIGNED NOT NULL,
  `sign` bigint(12) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `zarc`
--

CREATE TABLE `zarc` (
  `uid` varchar(8) NOT NULL,
  `total` bigint(12) UNSIGNED NOT NULL,
  `mru` bigint(12) UNSIGNED NOT NULL,
  `mfu` bigint(12) UNSIGNED NOT NULL,
  `anon` bigint(12) UNSIGNED NOT NULL,
  `header` bigint(12) UNSIGNED NOT NULL,
  `other` bigint(12) UNSIGNED NOT NULL,
  `compressed` bigint(12) UNSIGNED NOT NULL,
  `uncompressed` bigint(12) UNSIGNED NOT NULL,
  `ratio` bigint(12) UNSIGNED NOT NULL,
  `sign` bigint(12) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dev`
--
ALTER TABLE `dev`
  ADD UNIQUE KEY `sid` (`sid`);

--
-- Indexes for table `disk`
--
ALTER TABLE `disk`
  ADD UNIQUE KEY `uid` (`uid`,`sign`) USING BTREE;

--
-- Indexes for table `inet`
--
ALTER TABLE `inet`
  ADD UNIQUE KEY `uid` (`uid`,`sign`) USING BTREE;

--
-- Indexes for table `kern`
--
ALTER TABLE `kern`
  ADD UNIQUE KEY `uid` (`uid`,`sign`) USING BTREE;

--
-- Indexes for table `load`
--
ALTER TABLE `load`
  ADD UNIQUE KEY `uid` (`uid`,`sign`) USING BTREE;

--
-- Indexes for table `mem`
--
ALTER TABLE `mem`
  ADD UNIQUE KEY `uid` (`uid`,`sign`) USING BTREE;

--
-- Indexes for table `swap`
--
ALTER TABLE `swap`
  ADD UNIQUE KEY `uid` (`uid`,`sign`) USING BTREE;

--
-- Indexes for table `zarc`
--
ALTER TABLE `zarc`
  ADD UNIQUE KEY `uid` (`uid`,`sign`) USING BTREE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
